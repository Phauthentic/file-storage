<?php

/**
 * Copyright (c) Florian Krämer (https://florian-kraemer.net)
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Florian Krämer (https://florian-kraemer.net)
 * @author    Florian Krämer
 * @link      https://github.com/Phauthentic
 * @license   https://opensource.org/licenses/MIT MIT License
 */

declare(strict_types=1);

namespace Phauthentic\Infrastructure\Storage\Processor\Image;

use GuzzleHttp\Psr7\StreamWrapper;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use InvalidArgumentException;
use League\Flysystem\Config;
use Phauthentic\Infrastructure\Storage\FileInterface;
use Phauthentic\Infrastructure\Storage\Processor\Image\Exception\TempFileCreationFailedException;
use Phauthentic\Infrastructure\Storage\Processor\Image\Exception\UnsupportedOperationException;
use Phauthentic\Infrastructure\Storage\PathBuilder\PathBuilderInterface;
use Phauthentic\Infrastructure\Storage\FileStorageInterface;
use Phauthentic\Infrastructure\Storage\Processor\ProcessorInterface;
use Phauthentic\Infrastructure\Storage\Utility\TemporaryFile;

use function Phauthentic\Infrastructure\Storage\fopen;

/**
 * Image Operator
 */
class ImageProcessor implements ProcessorInterface
{
    use OptimizerTrait;

    /**
     * @var array
     */
    protected array $mimeTypes = [
        'image/gif',
        'image/jpg',
        'image/jpeg',
        'image/png'
    ];

    /**
     * @var array
     */
    protected array $processOnlyTheseVariants = [];

    /**
     * @var \Phauthentic\Infrastructure\Storage\FileStorageInterface
     */
    protected FileStorageInterface $storageHandler;

    /**
     * @var \Phauthentic\Infrastructure\Storage\PathBuilder\PathBuilderInterface
     */
    protected PathBuilderInterface $pathBuilder;

    /**
     * @var \Intervention\Image\ImageManager
     */
    protected ImageManager $imageManager;

    /**
     * @var \Intervention\Image\Image
     */
    protected Image $image;

    /**
     * @param \Phauthentic\Infrastructure\Storage\FileStorageInterface $storageHandler File Storage Handler
     * @param \Phauthentic\Infrastructure\Storage\PathBuilder\PathBuilderInterface $pathBuilder Path Builder
     * @param \Intervention\Image\ImageManager $imageManager Image Manager
     */
    public function __construct(
        FileStorageInterface $storageHandler,
        PathBuilderInterface $pathBuilder,
        ImageManager $imageManager
    ) {
        $this->storageHandler = $storageHandler;
        $this->pathBuilder = $pathBuilder;
        $this->imageManager = $imageManager;
    }

    /**
     * @param array $mimeTypes Mime Type List
     * @return $this
     */
    protected function setMimeTypes(array $mimeTypes): self
    {
        $this->mimeTypes = $mimeTypes;

        return $this;
    }

    /**
     * @param \Phauthentic\Infrastructure\Storage\FileInterface $file File
     * @return bool
     */
    protected function isApplicable(FileInterface $file): bool
    {
        return $file->hasVariants()
            && in_array($file->mimeType(), $this->mimeTypes, true);
    }

    /**
     * @param array $variants Variants by name
     * @return $this
     */
    public function processOnlyTheseVariants(array $variants): self
    {
        $this->processOnlyTheseVariants = $variants;

        return $this;
    }

    /**
     * @return $this
     */
    public function processAll(): self
    {
        $this->processOnlyTheseVariants = [];

        return $this;
    }

    /**
     * Read the data from the files resource if (still) present,
     * if not fetch it from the storage backend and write the data
     * to the stream of the temp file
     *
     * @param \Phauthentic\Infrastructure\Storage\FileInterface $file File
     * @param resource $tempFileStream Temp File Stream Resource
     * @return int|bool False on error
     */
    protected function copyOriginalFileData(FileInterface $file, $tempFileStream)
    {
        $stream = $file->resource();
        $storage = $this->storageHandler->getStorage($file->storage());

        if ($stream === null) {
            $stream = $storage->readStream($file->path());
            $stream = $stream['stream'];
        } else {
            rewind($stream);
        }
        $result = stream_copy_to_stream(
            $stream,
            $tempFileStream
        );
        fclose($tempFileStream);

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function process(FileInterface $file): FileInterface
    {
        if (!$this->isApplicable($file)) {
            return $file;
        }

        $storage = $this->storageHandler->getStorage($file->storage());

        // Create a local tmp file on the processing system / machine
        $tempFile = TemporaryFile::create();
        $tempFileStream = fopen($tempFile, 'wb+');

        // Read the data from the files resource if (still) present,
        // if not fetch it from the storage backend and write the data
        // to the stream of the temp file
        $result = $this->copyOriginalFileData($file, $tempFileStream);

        // Stop if the temp file could not be generated
        if ($result === false) {
            throw TempFileCreationFailedException::withFilename($tempFile);
        }

        // Iterate over the variants described as an array
        foreach ($file->variants() as $variant => $data) {
            if (
                empty($data['operations'])
                || (
                    !empty($this->processOnlyTheseVariants)
                    && !in_array($variant, $this->processOnlyTheseVariants, true)
                )
            ) {
                continue;
            }

            $this->image = $this->imageManager->make($tempFile);

            // Apply the operations
            foreach ($data['operations'] as $operation => $arguments) {
                if (!method_exists($this, $operation)) {
                    throw UnsupportedOperationException::withName($operation);
                }

                $this->$operation($arguments);
            }

            $path = $this->pathBuilder->pathForVariant($file, $variant);

            if (isset($data['optimize']) && $data['optimize'] === true) {
                $this->optimizeAndStore($file, $path);
            } else {
                $storage->writeStream(
                    $path,
                    StreamWrapper::getResource($this->image->stream($file->extension(), 90)),
                    new Config()
                );
            }

            $data['path'] = $path;
            $file = $file->withVariant($variant, $data);
        }

        unlink($tempFile);

        return $file;
    }

    /**
     * @param \Phauthentic\Infrastructure\Storage\FileInterface $file File
     * @param string $path Path
     * @return void
     */
    protected function optimizeAndStore(FileInterface $file, string $path): void
    {
        $storage = $this->storageHandler->getStorage($file->storage());

        // We need more tmp files because the optimizer likes to write
        // and read the files from disk, not from a stream. :(
        $optimizerTempFile = TemporaryFile::create();
        $optimizerOutput = TemporaryFile::create();

        // Save the image to the tmp file
        $this->image->save($optimizerTempFile, 90, $file->extension());
        // Optimize it and write it to another file
        $this->optimizer()->optimize($optimizerTempFile, $optimizerOutput);
        // Open a new stream for the storage system
        $optimizerOutputHandler = fopen($optimizerOutput, 'rb+');

        // And store it...
        $storage->writeStream(
            $path,
            $optimizerOutputHandler,
            new Config()
        );

        // Cleanup
        fclose($optimizerOutputHandler);
        unlink($optimizerTempFile);
        unlink($optimizerOutput);

        // Cleanup
        unset(
            $optimizerOutputHandler,
            $optimizerTempFile,
            $optimizerOutput
        );
    }

    /**
     * Crops the image
     *
     * @link http://image.intervention.io/api/fit
     * @param array $arguments Arguments
     * @return void
     */
    protected function fit(array $arguments): void
    {
        if (!isset($arguments['width'])) {
            throw new InvalidArgumentException('Missing width');
        }

        $preventUpscale = $arguments['preventUpscale'] ?? false;
        $height = $arguments['height'] ?? null;

        $this->image->fit(
            (int)$arguments['width'],
            (int)$height,
            static function ($constraint) use ($preventUpscale) {
                if ($preventUpscale) {
                    $constraint->upsize();
                }
            }
        );
    }

    /**
     * Crops the image
     *
     * @link http://image.intervention.io/api/crop
     * @param array $arguments Arguments
     * @return void
     */
    protected function crop(array $arguments): void
    {
        if (!isset($arguments['height'], $arguments['width'])) {
            throw new InvalidArgumentException('Missing height or width');
        }

        $height = $arguments['height'] ? (int)$arguments['height'] : null;
        $width = $arguments['width'] ? (int)$arguments['width'] : null;
        $x = $arguments['x'] ? (int)$arguments['x'] : null;
        $y = $arguments['y'] ? (int)$arguments['y'] : null;

        $this->image->crop($width, $height, $x, $y);
    }

    /**
     * Flips the image horizontal
     *
     * @link http://image.intervention.io/api/flip
     * @param array $arguments Arguments
     * @return void
     */
    protected function flipHorizontal(array $arguments): void
    {
        $this->flip(['direction' => 'h']);
    }

    /**
     * Flips the image vertical
     *
     * @link http://image.intervention.io/api/flip
     * @param array $arguments Arguments
     * @return void
     */
    protected function flipVertical(array $arguments): void
    {
        $this->flip(['direction' => 'v']);
    }

    /**
     * Flips the image
     *
     * @link http://image.intervention.io/api/flip
     * @param array $arguments Arguments
     * @return void
     */
    protected function flip(array $arguments): void
    {
        if (!isset($arguments['direction'])) {
            throw new InvalidArgumentException('Direction missing');
        }

        if ($arguments['direction'] !== 'v' && $arguments['direction'] !== 'h') {
            throw new InvalidArgumentException(
                'Invalid argument, you must provide h or v'
            );
        }

        $this->image->flip($arguments['direction']);
    }

    /**
     * Resizes the image
     *
     * @param array $arguments Arguments
     * @return void
     */
    protected function resize(array $arguments): void
    {
        if (!isset($arguments['height'], $arguments['width'])) {
            throw new InvalidArgumentException(
                'Missing height or width'
            );
        }

        $aspectRatio = $arguments['aspectRatio'] ?? true;
        $preventUpscale = $arguments['preventUpscale'] ?? false;

        $this->image->resize(
            $arguments['width'],
            $arguments['height'],
            static function ($constraint) use ($aspectRatio, $preventUpscale) {
                if ($aspectRatio) {
                    $constraint->aspectRatio();
                }
                if ($preventUpscale) {
                    $constraint->upsize();
                }
            }
        );
    }
}
