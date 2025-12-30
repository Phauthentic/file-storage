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
use Phauthentic\Infrastructure\Storage\UrlBuilder\LocalUrlBuilder;
use Phauthentic\Infrastructure\Storage\UrlBuilder\UrlBuilderInterface;
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

    /**
     * @return void
     */
    public function testUrlBuilderIntegration(): void
    {
        $ds = DIRECTORY_SEPARATOR;

        $storageService = new StorageService(
            new StorageAdapterFactory(),
        );

        $storageService->setAdapterConfigFromArray([
            'local' => [
                'class' => LocalFactory::class,
                'options' => [
                    'root' => $this->storageRoot . $ds . 'storage_url_builder' . $ds
                ]
            ],
        ]);

        $urlBuilder = new LocalUrlBuilder('/files');
        $fileStorage = new FileStorage(
            $storageService,
            new PathBuilder(),
            $urlBuilder
        );

        $fileOnDisk = $this->getFixtureFile('titus.jpg');
        $file = FileFactory::fromDisk($fileOnDisk, 'local')
            ->withUuid('914e1512-9153-4253-a81e-7ee2edc1d976')
            ->belongsToModel('User', '1');

        $file = $fileStorage->store($file);

        // URL should be built by the URL builder
        $this->assertNotEmpty($file->url());
        $this->assertStringStartsWith('/files', $file->url());

        $file = $fileStorage->remove($file);
    }

    /**
     * @return void
     */
    public function testMultipleCallbacks(): void
    {
        $ds = DIRECTORY_SEPARATOR;

        $storageService = new StorageService(
            new StorageAdapterFactory(),
        );

        $storageService->setAdapterConfigFromArray([
            'local' => [
                'class' => LocalFactory::class,
                'options' => [
                    'root' => $this->storageRoot . $ds . 'storage_multi_callbacks' . $ds
                ]
            ],
        ]);

        $fileStorage = new FileStorage($storageService, new PathBuilder());

        $callbackOrder = [];
        $fileStorage->addCallback('beforeSave', function ($file) use (&$callbackOrder) {
            $callbackOrder[] = 'before1';
            return $file;
        });
        $fileStorage->addCallback('beforeSave', function ($file) use (&$callbackOrder) {
            $callbackOrder[] = 'before2';
            return $file;
        });
        $fileStorage->addCallback('afterSave', function ($file) use (&$callbackOrder) {
            $callbackOrder[] = 'after1';
            return $file;
        });
        $fileStorage->addCallback('afterSave', function ($file) use (&$callbackOrder) {
            $callbackOrder[] = 'after2';
            return $file;
        });

        $fileOnDisk = $this->getFixtureFile('titus.jpg');
        $file = FileFactory::fromDisk($fileOnDisk, 'local')
            ->withUuid('914e1512-9153-4253-a81e-7ee2edc1d977');

        $fileStorage->store($file);

        $expectedOrder = ['before1', 'before2', 'after1', 'after2'];
        $this->assertEquals($expectedOrder, $callbackOrder);
    }

    /**
     * @return void
     */
    public function testCallbackCanModifyFile(): void
    {
        $ds = DIRECTORY_SEPARATOR;

        $storageService = new StorageService(
            new StorageAdapterFactory(),
        );

        $storageService->setAdapterConfigFromArray([
            'local' => [
                'class' => LocalFactory::class,
                'options' => [
                    'root' => $this->storageRoot . $ds . 'storage_callback_modify' . $ds
                ]
            ],
        ]);

        $fileStorage = new FileStorage($storageService, new PathBuilder());

        $fileStorage->addCallback('beforeSave', function ($file) {
            return $file->withFilename('modified_by_callback.jpg');
        });

        $fileOnDisk = $this->getFixtureFile('titus.jpg');
        $file = FileFactory::fromDisk($fileOnDisk, 'local')
            ->withUuid('914e1512-9153-4253-a81e-7ee2edc1d978')
            ->withFilename('original.jpg');

        $file = $fileStorage->store($file);

        // File name should have been modified by callback
        $this->assertEquals('modified_by_callback.jpg', $file->filename());

        $file = $fileStorage->remove($file);
    }

    /**
     * @return void
     */
    public function testAfterRemoveCallback(): void
    {
        $ds = DIRECTORY_SEPARATOR;

        $storageService = new StorageService(
            new StorageAdapterFactory(),
        );

        $storageService->setAdapterConfigFromArray([
            'local' => [
                'class' => LocalFactory::class,
                'options' => [
                    'root' => $this->storageRoot . $ds . 'storage_after_remove' . $ds
                ]
            ],
        ]);

        $fileStorage = new FileStorage($storageService, new PathBuilder());

        $callbackCalled = false;
        $fileStorage->addCallback('afterRemove', function ($file) use (&$callbackCalled) {
            $callbackCalled = true;
            return $file;
        });

        $fileOnDisk = $this->getFixtureFile('titus.jpg');
        $file = FileFactory::fromDisk($fileOnDisk, 'local')
            ->withUuid('914e1512-9153-4253-a81e-7ee2edc1d979');

        $file = $fileStorage->store($file);
        $file = $fileStorage->remove($file);

        $this->assertTrue($callbackCalled);
    }

    /**
     * @return void
     */
    public function testBeforeRemoveCallback(): void
    {
        $ds = DIRECTORY_SEPARATOR;

        $storageService = new StorageService(
            new StorageAdapterFactory(),
        );

        $storageService->setAdapterConfigFromArray([
            'local' => [
                'class' => LocalFactory::class,
                'options' => [
                    'root' => $this->storageRoot . $ds . 'storage_before_remove' . $ds
                ]
            ],
        ]);

        $fileStorage = new FileStorage($storageService, new PathBuilder());

        $callbackCalled = false;
        $fileStorage->addCallback('beforeRemove', function ($file) use (&$callbackCalled) {
            $callbackCalled = true;
            return $file;
        });

        $fileOnDisk = $this->getFixtureFile('titus.jpg');
        $file = FileFactory::fromDisk($fileOnDisk, 'local')
            ->withUuid('914e1512-9153-4253-a81e-7ee2edc1d980');

        $file = $fileStorage->store($file);
        $file = $fileStorage->remove($file);

        $this->assertTrue($callbackCalled);
    }

    /**
     * @return void
     */
    public function testRemoveWithMultipleVariants(): void
    {
        $ds = DIRECTORY_SEPARATOR;

        $storageService = new StorageService(
            new StorageAdapterFactory(),
        );

        $storageService->setAdapterConfigFromArray([
            'local' => [
                'class' => LocalFactory::class,
                'options' => [
                    'root' => $this->storageRoot . $ds . 'storage_remove_variants' . $ds
                ]
            ],
        ]);

        $fileStorage = new FileStorage($storageService, new PathBuilder());

        $fileOnDisk = $this->getFixtureFile('titus.jpg');
        $file = FileFactory::fromDisk($fileOnDisk, 'local')
            ->withUuid('914e1512-9153-4253-a81e-7ee2edc1d981')
            ->withVariant('thumb', ['path' => 'thumb/path.jpg', 'width' => 100])
            ->withVariant('medium', ['path' => 'medium/path.jpg', 'width' => 300])
            ->withVariant('large', ['path' => 'large/path.jpg', 'width' => 800]);

        $file = $fileStorage->store($file);

        // Remove should delete all variants and the main file from storage
        // but the file object still contains the variant definitions
        $file = $fileStorage->remove($file);

        $this->assertTrue($file->hasVariants()); // Variants are still in the file object
    }

    /**
     * @return void
     */
    public function testStoreWithConfigParameter(): void
    {
        $ds = DIRECTORY_SEPARATOR;

        $storageService = new StorageService(
            new StorageAdapterFactory(),
        );

        $storageService->setAdapterConfigFromArray([
            'local' => [
                'class' => LocalFactory::class,
                'options' => [
                    'root' => $this->storageRoot . $ds . 'storage_with_config' . $ds
                ]
            ],
        ]);

        $fileStorage = new FileStorage($storageService, new PathBuilder());

        $fileOnDisk = $this->getFixtureFile('titus.jpg');
        $file = FileFactory::fromDisk($fileOnDisk, 'local')
            ->withUuid('914e1512-9153-4253-a81e-7ee2edc1d982');

        // Test with Config parameter
        $config = new \League\Flysystem\Config(['visibility' => 'private']);
        $file = $fileStorage->store($file, $config);

        $this->assertNotEmpty($file->path());

        $file = $fileStorage->remove($file);
    }


    /**
     * @return void
     */
    public function testRemoveWithAdapterFailure(): void
    {
        $storageService = $this->createMock(StorageService::class);
        $adapter = $this->createMock(\League\Flysystem\AdapterInterface::class);

        $storageService->expects($this->any())
            ->method('adapter')
            ->willReturn($adapter);

        $adapter->expects($this->once())
            ->method('delete')
            ->willReturn(false);

        $fileStorage = new FileStorage($storageService);

        $file = File::create('test.jpg', 1000, 'image/jpeg', 'local')
            ->withPath('/test/path.jpg');

        // This should not throw an exception - remove() doesn't check delete() return value
        $result = $fileStorage->remove($file);
        $this->assertInstanceOf(\Phauthentic\Infrastructure\Storage\FileInterface::class, $result);
    }
}
