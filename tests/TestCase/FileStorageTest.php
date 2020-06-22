<?php

declare(strict_types=1);

namespace Phauthentic\Test\TestCase;

use Phauthentic\Infrastructure\Storage\FileFactory;
use Phauthentic\Infrastructure\Storage\FileStorage;
use Phauthentic\Infrastructure\Storage\StorageAdapterFactory;
use Phauthentic\Infrastructure\Storage\StorageService;

/**
 * File Storage Test
 */
class FileStorageTest extends TestCase
{
    /**
     * @return void
     */
    public function testFileStorage(): void
    {
        $fileStorage = new FileStorage(
            new StorageService(
                new StorageAdapterFactory()
            )
        );

        $fileOnDisk = $this->getFixtureFile('titus.jpg');

        $file = FileFactory::fromDisk($fileOnDisk, 'local')
            ->withUuid('914e1512-9153-4253-a81e-7ee2edc1d973')
            ->belongsToModel('User', '1')
            ->withMetadataKey('bar', 'foo');

        $file = $fileStorage->store($file);

        $this->assertNotEmpty($file->path());
    }
}
