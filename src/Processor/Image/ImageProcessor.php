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
use League\Flysystem\Config;
use Phauthentic\Infrastructure\Storage\FileInterface;
use Phauthentic\Infrastructure\Storage\Processor\Image\Exception\TempFileCreationFailedException;
use Phauthentic\Infrastructure\Storage\Processor\Image\Exception\UnsupportedOperationException;
use Phauthentic\Infrastructure\Storage\PathBuilder\PathBuilderInterface;
use Phauthentic\Infrastructure\Storage\FileStorageInterface;
use Phauthentic\Infrastructure\Storage\Processor\ProcessorInterface;
use Phauthentic\Infrastructure\Storage\Utility\TemporaryFile;
use InvalidArgumentException;

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
    protected array $processOnlyTheseVersions = [];

    protected FileStorageInterface $storageHandler;

    protected PathBuilderInterface $pathBuilder;

    protected ImageManager $imageManager;

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
        return in_array($file->mimeType(), $this->mimeTypes, true);
    }

    /**
     * @param array $manipulations Manipulations
     * @return $this
     */
    public function processOnlyTheseVersions(array $manipulations): self
    {
        $this->processOnlyTheseVersions = $manipulations;

        return $this;
    }

    /**
     * @return $this
     */
    public function processAll(): self
    {
        $this->processOnlyTheseVersions = [];

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function process(FileInterface $file): FileInterface
    {
        if (!$this->isApplicable($file)) {
            return $file;
        }

        // Create a local tmp file on the processing system / machine
        $tempFile = TemporaryFile::create();
        $tempFileStream = fopen($tempFile, 'wb+');

        // Get the file from the storage system and copy it to the temp file
        $storage = $this->storageHandler->getStorage($file->storage());
        $stream = $storage->readStream($file->path());
        $result = stream_copy_to_stream(
            $stream['stream'],
            $tempFileStream
        );
        fclose($tempFileStream);
        unset($tempFileStream);

        // Stop if the temp file could not be generated
        if ($result === false) {
            throw TempFileCreationFailedException::withFilename($tempFile);
        }

        // Iterate over the manipulations described as an array
        foreach ($file->manipulations() as $manipulation => $data) {
            if (
                empty($data['operations'])
                || (
                    !empty($this->processOnlyTheseVersions)
                    && !in_array($manipulation, $this->processOnlyTheseVersions, true)
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

            $path = $this->pathBuilder->pathForManipulation($file, $manipulation);

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
            $file = $file->withManipulation($manipulation, $data);
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
