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

use Phauthentic\Infrastructure\Storage\File;
use Phauthentic\Infrastructure\Storage\FileFactory;
use Phauthentic\Infrastructure\Storage\FileInterface;
use Phauthentic\Infrastructure\Storage\PathBuilder\PathBuilder;
use Phauthentic\Infrastructure\Storage\Utility\MimeType;
use Phauthentic\Infrastructure\Storage\Utility\PathInfo;
use RuntimeException;

/**
 * File Test
 */
class FileTest extends TestCase
{
    /**
     * @return void
     */
    public function testCreate(): void
    {
        $fileOnDisk = $this->getFixtureFile('titus.jpg');
        $info = PathInfo::for($fileOnDisk);
        $filesize = filesize($fileOnDisk);
        $mimeType = MimeType::byExtension($info->extension());

        $file = File::create(
            $info->basename(),
            $filesize,
            $mimeType,
            'local',
        );

        $this->assertInstanceOf(FileInterface::class, $file);
    }

    /**
     * @return void
     */
    public function testFile(): void
    {
        $fileOnDisk = $this->getFixtureFile('titus.jpg');
        $pathBuilder = new PathBuilder();

        $file = FileFactory::fromDisk($fileOnDisk, 'local')
            ->withUuid('914e1512-9153-4253-a81e-7ee2edc1d973')
            ->withFilename('foobar.jpg')
            ->addToCollection('avatar')
            ->belongsToModel('User', '1')
            ->withMetadata([
                'one' => 'two',
                'two' => 'one'
            ])
            ->withMetadataKey('bar', 'foo');

        $file = $file->buildPath($pathBuilder);

        $expectedMetadata = [
            'one' => 'two',
            'two' => 'one',
            'bar' => 'foo'
        ];

        $this->assertEquals('914e1512-9153-4253-a81e-7ee2edc1d973', $file->uuid());
        $this->assertEquals('foobar.jpg', $file->filename());
        $this->assertEquals('image/jpeg', $file->mimeType());
        $this->assertEquals('avatar', $file->collection());
        $this->assertEquals('User', $file->model());
        $this->assertEquals('1', $file->modelId());
        $this->assertEquals($expectedMetadata, $file->metadata());
        $this->assertTrue(is_resource($file->resource()));
        $this->assertEquals($this->sanitizeSeparator('User\fe\c3\b4\914e151291534253a81e7ee2edc1d973\foobar.jpg'), $file->path());
        $this->assertEquals(332643, $file->filesize());
        $this->assertFalse($file->hasVariants());
        $this->assertFalse($file->hasVariant('somemanipulation'));
        $this->assertIsArray($file->toArray());
        $this->assertIsString(json_encode($file));

        $file = $file->withoutMetadataKey('bar');
        $expectedMetadata = [
            'one' => 'two',
            'two' => 'one',
        ];
        $this->assertEquals($expectedMetadata, $file->metadata());

        $path = '/test/path/file.jpg';
        $file = $file->withPath($path);
        $this->assertEquals($path, $file->path());

        $expected = [
            'uuid' => '914e1512-9153-4253-a81e-7ee2edc1d973',
            'filename' => 'foobar.jpg',
            'filesize' => 332643,
            'mimeType' => 'image/jpeg',
            'extension' => 'jpg',
            'path' => '/test/path/file.jpg',
            'model' => 'User',
            'modelId' => '1',
            'collection' => 'avatar',
            'readableSize' => '325kB',
            'variants' => [],
            'metaData' => [
                'one' => 'two',
                'two' => 'one',
            ],
            'url' => '',
        ];
        $this->assertEquals($expected, $file->toArray());

        $file = $file->withoutMetadata();
        $this->assertEmpty($file->metadata());

        $this->assertEmpty($file->variants());
    }

    /**
     * @return void
     */
    public function testPathException(): void
    {
        $fileOnDisk = $this->getFixtureFile('titus.jpg');

        $file = FileFactory::fromDisk($fileOnDisk, 'local')
            ->withUuid('914e1512-9153-4253-a81e-7ee2edc1d973');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Path has not been set');
        $file->path();
    }
}
