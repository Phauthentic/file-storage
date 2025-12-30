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

namespace Phauthentic\Storage\Test\TestCase;

use League\Flysystem\Adapter\Local;
use Phauthentic\Infrastructure\Storage\Exception\StorageException;
use Phauthentic\Infrastructure\Storage\Factories\Exception\FactoryNotFoundException;
use Phauthentic\Infrastructure\Storage\StorageAdapterFactory;
use Phauthentic\Infrastructure\Storage\StorageAdapterFactoryInterface;
use Phauthentic\Infrastructure\Storage\StorageService;
use Phauthentic\Test\TestCase\TestCase;
use RuntimeException;

/**
 * StorageTest
 */
class StorageServiceTest extends TestCase
{
    /**
     * @return void
     */
    public function testStorage(): void
    {
        $service = new StorageService(
            new StorageAdapterFactory()
        );

        $this->assertFalse($service->adapters()->has('local'));

        $service->setAdapterConfigFromArray([
            'local' => [
                'class' => 'Local',
                'options' => [
                    'root' => $this->storageRoot
                ]
            ]
        ]);

        $adapter = $service->adapter('local');
        $this->assertTrue($service->adapters()->has('local'));
        $this->assertInstanceOf(Local::class, $adapter);

        $result = $service->adapterFactory();
        $this->assertInstanceOf(StorageAdapterFactoryInterface::class, $result);

        $this->assertFalse($service->fileExists('local', 'doesnot'));

        $result = $service->storeFile(
            'local',
            '/horse/photo.jpg',
            $this->getFixtureFile('titus.jpg')
        );
        $this->assertIsArray($result);

        $result = $service->storeResource(
            'local',
            '/horse/photo.jpg',
            fopen($this->getFixtureFile('titus.jpg'), 'rb')
        );
        $this->assertIsArray($result);

        $result = $service->removeFile('local', '/horse/photo.jpg');
        $this->assertTrue($result);
    }

    /**
     * @return void
     */
    public function testSetAdapterConfigFromArrayMissingClass(): void
    {
        $service = new StorageService(new StorageAdapterFactory());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Adapter class or name is missing');

        $service->setAdapterConfigFromArray([
            'local' => [
                'options' => ['root' => $this->storageRoot]
                // Missing 'class' key
            ]
        ]);
    }

    /**
     * @return void
     */
    public function testSetAdapterConfigFromArrayMissingOptions(): void
    {
        $service = new StorageService(new StorageAdapterFactory());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Adapter options must be an array');

        $service->setAdapterConfigFromArray([
            'local' => [
                'class' => 'Local'
                // Missing 'options' key
            ]
        ]);
    }

    /**
     * @return void
     */
    public function testSetAdapterConfigFromArrayOptionsNotArray(): void
    {
        $service = new StorageService(new StorageAdapterFactory());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Adapter options must be an array');

        $service->setAdapterConfigFromArray([
            'local' => [
                'class' => 'Local',
                'options' => 'not_an_array'
            ]
        ]);
    }

    /**
     * @return void
     */
    public function testStoreResourceWithAdapterFailure(): void
    {
        $service = new StorageService(new StorageAdapterFactory());

        $service->setAdapterConfigFromArray([
            'local' => [
                'class' => 'Local',
                'options' => [
                    'root' => $this->storageRoot
                ]
            ]
        ]);

        $adapter = $this->createMock(\League\Flysystem\AdapterInterface::class);
        $service->adapters()->add('local', $adapter);

        $adapter->expects($this->once())
            ->method('writeStream')
            ->willReturn(false);

        $this->expectException(StorageException::class);
        $this->expectExceptionMessage('Failed to store resource stream to in `local` with path `test.txt`');

        $resource = fopen('php://temp', 'r+');
        $service->storeResource('local', 'test.txt', $resource);
    }


    /**
     * @return void
     */
    public function testStoreFileWithWriteFailure(): void
    {
        $service = new StorageService(new StorageAdapterFactory());

        $service->setAdapterConfigFromArray([
            'local' => [
                'class' => 'Local',
                'options' => [
                    'root' => $this->storageRoot
                ]
            ]
        ]);

        $adapter = $this->createMock(\League\Flysystem\AdapterInterface::class);
        $service->adapters()->add('local', $adapter);

        $adapter->expects($this->once())
            ->method('write')
            ->willReturn(false);

        $this->expectException(StorageException::class);
        $this->expectExceptionMessage('Failed to store file `');

        $service->storeFile('local', 'test.txt', $this->getFixtureFile('titus.jpg'));
    }

    /**
     * @return void
     */
    public function testAdapterWithNonExistentConfig(): void
    {
        $service = new StorageService(new StorageAdapterFactory());

        $this->expectException(FactoryNotFoundException::class);

        $service->adapter('nonexistent');
    }

    /**
     * @return void
     */
    public function testAddAdapterConfig(): void
    {
        $service = new StorageService(new StorageAdapterFactory());

        $this->assertFalse($service->adapters()->has('test_adapter'));

        $service->addAdapterConfig('test_adapter', 'Local', ['root' => $this->storageRoot]);

        // Should create adapter when accessed
        $adapter = $service->adapter('test_adapter');
        $this->assertInstanceOf(Local::class, $adapter);
        $this->assertTrue($service->adapters()->has('test_adapter'));
    }

    /**
     * @return void
     */
    public function testAdapterLazyLoading(): void
    {
        $service = new StorageService(new StorageAdapterFactory());

        $service->setAdapterConfigFromArray([
            'local1' => [
                'class' => 'Local',
                'options' => ['root' => $this->storageRoot]
            ],
            'local2' => [
                'class' => 'Local',
                'options' => ['root' => $this->storageRoot]
            ]
        ]);

        // First access should create and cache the adapter
        $adapter1 = $service->adapter('local1');
        $this->assertInstanceOf(Local::class, $adapter1);

        // Second access should return the same cached instance
        $adapter1Again = $service->adapter('local1');
        $this->assertSame($adapter1, $adapter1Again);

        // Different adapter should be different instance
        $adapter2 = $service->adapter('local2');
        $this->assertInstanceOf(Local::class, $adapter2);
        $this->assertNotSame($adapter1, $adapter2);
    }
}
