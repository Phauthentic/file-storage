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

namespace Phauthentic\Infrastructure\Storage;

use GuzzleHttp\Psr7\StreamWrapper;
use Phauthentic\Infrastructure\Storage\Exception\FileDoesNotExistException;
use Phauthentic\Infrastructure\Storage\Exception\FileNotReadableException;
use Phauthentic\Infrastructure\Storage\Utility\MimeType;
use Phauthentic\Infrastructure\Storage\Utility\PathInfo;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

/**
 * File Factory
 */
class FileFactory implements FileFactoryInterface
{
    /**
     * @inheritDoc
     */
    public static function fromUploadedFile(
        UploadedFileInterface $uploadedFile,
        string $storage
    ): FileInterface {
        static::checkUploadedFile($uploadedFile);

        $file = File::create(
            $uploadedFile->getClientFilename(),
            $uploadedFile->getSize(),
            $uploadedFile->getClientMediaType(),
            $storage
        );

        return $file->withResource(
            StreamWrapper::getResource($uploadedFile->getStream())
        );
    }

    /**
     * @inheritDoc
     */
    public static function fromDisk(string $path, string $storage): FileInterface
    {
        static::checkFile($path);

        $info = PathInfo::for($path);
        $filesize = filesize($path);
        $mimeType = MimeType::byExtension($info->extension());

        $file = File::create(
            $info->basename(),
            $filesize,
            $mimeType,
            $storage,
        );

        return $file->withResource(fopen($path, 'rb'));
    }

    /**
     * Checks if the uploaded file is a valid upload
     *
     * @param \Psr\Http\Message\UploadedFileInterface $uploadedFile Uploaded File
     * @return void
     */
    protected static function checkUploadedFile(UploadedFileInterface $uploadedFile): void
    {
        if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
            throw new RuntimeException(sprintf(
                'Can\'t create storage object from upload with error code: %d',
                $uploadedFile->getError()
            ));
        }
    }

    /**
     * @param string $path Path
     * @return void
     */
    protected static function checkFile(string $path): void
    {
        if (!file_exists($path)) {
            throw FileDoesNotExistException::filename($path);
        }

        if (!is_readable($path)) {
            throw FileNotReadableException::filename($path);
        }
    }
}
