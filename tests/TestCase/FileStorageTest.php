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

use Phauthentic\Infrastructure\Storage\Factories\LocalFactory;
use Phauthentic\Infrastructure\Storage\FileFactory;
use Phauthentic\Infrastructure\Storage\FileStorage;
use Phauthentic\Infrastructure\Storage\PathBuilder\PathBuilder;
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
            ->withMetadataKey('bar', 'foo');

        $file = $fileStorage->store($file);

        $this->assertNotEmpty($file->path());

        $file = $fileStorage->remove($file);
    }
}
