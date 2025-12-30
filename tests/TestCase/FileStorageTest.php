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

namespace Phauthentic\Test\TestCase;

use InvalidArgumentException;
use League\Flysystem\Adapter\NullAdapter;
use Phauthentic\Infrastructure\Storage\Factories\LocalFactory;
use Phauthentic\Infrastructure\Storage\File;
use Phauthentic\Infrastructure\Storage\FileFactory;
use Phauthentic\Infrastructure\Storage\FileStorage;
use Phauthentic\Infrastructure\Storage\PathBuilder\PathBuilder;
use Phauthentic\Infrastructure\Storage\Processor\Exception\VariantDoesNotExistException;
use Phauthentic\Infrastructure\Storage\Processor\Exception\VariantException;
use Phauthentic\Infrastructure\Storage\StorageAdapterFactory;
use Phauthentic\Infrastructure\Storage\StorageService;
use RuntimeException;

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
        $ds = DIRECTORY_SEPARATOR;

        $storageService = new StorageService(
            new StorageAdapterFactory(),
        );

        $storageService->setAdapterConfigFromArray([
            'local' => [
                'class' => LocalFactory::class,
                'options' => [
                    'root' => $this->storageRoot . $ds . 'storage1' . $ds
                ]
            ],
        ]);

        $fileStorage = new FileStorage(
            $storageService,
            new PathBuilder()
        );

        $fileOnDisk = $this->getFixtureFile('titus.jpg');

        $file = FileFactory::fromDisk($fileOnDisk, 'local')
            ->withUuid('914e1512-9153-4253-a81e-7ee2edc1d973')
            ->belongsToModel('User', '1')
            ->withMetadataByKey('bar', 'foo');

        $file = $fileStorage->store($file);

        $this->assertNotEmpty($file->path());

        $file = $fileStorage->remove($file);
    }

    /**
     * @return void
     */
    public function testCallbacks(): void
    {
        $ds = DIRECTORY_SEPARATOR;

        $storageService = new StorageService(
            new StorageAdapterFactory(),
        );

        $storageService->setAdapterConfigFromArray([
            'local' => [
                'class' => LocalFactory::class,
                'options' => [
                    'root' => $this->storageRoot . $ds . 'storage2' . $ds
                ]
            ],
        ]);

        $fileStorage = new FileStorage($storageService, new PathBuilder());

        $callbackCalled = false;
        $fileStorage->addCallback('beforeSave', function ($file) use (&$callbackCalled) {
            $callbackCalled = true;
            return $file;
        });

        $fileOnDisk = $this->getFixtureFile('titus.jpg');
        $file = FileFactory::fromDisk($fileOnDisk, 'local')
            ->withUuid('914e1512-9153-4253-a81e-7ee2edc1d974');

        $fileStorage->store($file);

        $this->assertTrue($callbackCalled);
    }

    /**
     * @return void
     */
    public function testInvalidCallbackName(): void
    {
        $storageService = new StorageService(new StorageAdapterFactory());
        $fileStorage = new FileStorage($storageService);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid callback `invalidCallback`, only beforeSave, afterSave, beforeRemove, afterRemove are valid');

        $fileStorage->addCallback('invalidCallback', function () {});
    }

    /**
     * @return void
     */
    public function testRemoveVariant(): void
    {
        $ds = DIRECTORY_SEPARATOR;

        $storageService = new StorageService(
            new StorageAdapterFactory(),
        );

        $storageService->setAdapterConfigFromArray([
            'local' => [
                'class' => LocalFactory::class,
                'options' => [
                    'root' => $this->storageRoot . $ds . 'storage3' . $ds
                ]
            ],
        ]);

        $fileStorage = new FileStorage($storageService, new PathBuilder());

        $fileOnDisk = $this->getFixtureFile('titus.jpg');
        $file = FileFactory::fromDisk($fileOnDisk, 'local')
            ->withUuid('914e1512-9153-4253-a81e-7ee2edc1d975')
            ->withVariant('thumb', ['path' => 'thumb/path.jpg', 'width' => 100]);

        $file = $fileStorage->store($file);

        // Now remove the variant
        $file = $fileStorage->removeVariant($file, 'thumb');

        $this->assertFalse($file->hasVariant('thumb'));
    }

    /**
     * @return void
     */
    public function testRemoveVariantDoesNotExist(): void
    {
        $storageService = new StorageService(new StorageAdapterFactory());
        $fileStorage = new FileStorage($storageService);

        $file = FileFactory::fromDisk($this->getFixtureFile('titus.jpg'), 'local');

        $this->expectException(VariantDoesNotExistException::class);

        $fileStorage->removeVariant($file, 'nonexistent');
    }

    /**
     * @return void
     */
    public function testRemoveVariantMissingPath(): void
    {
        $storageService = new StorageService(new StorageAdapterFactory());
        $fileStorage = new FileStorage($storageService);

        $file = FileFactory::fromDisk($this->getFixtureFile('titus.jpg'), 'local')
            ->withVariant('thumb', ['width' => 100]); // No path

        $this->expectException(VariantException::class);
        $this->expectExceptionMessage('Variant `thumb` is missing a path');

        $fileStorage->removeVariant($file, 'thumb');
    }

    /**
     * @return void
     */
    public function testGetStorage(): void
    {
        $storageService = $this->createMock(StorageService::class);
        $adapter = new NullAdapter();

        $storageService->expects($this->once())
            ->method('adapter')
            ->with('test')
            ->willReturn($adapter);

        $fileStorage = new FileStorage($storageService);

        $result = $fileStorage->getStorage('test');
        $this->assertSame($adapter, $result);
    }

    /**
     * @return void
     */
    public function testStoreWithoutResource(): void
    {
        $storageService = $this->createMock(StorageService::class);
        $adapter = new NullAdapter();

        $storageService->expects($this->any())
            ->method('adapter')
            ->willReturn($adapter);

        $fileStorage = new FileStorage($storageService);

        $file = File::create('test.jpg', 1000, 'image/jpeg', 'local');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No resource given');

        $fileStorage->store($file);
    }
}
