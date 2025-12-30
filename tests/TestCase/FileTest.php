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

use Phauthentic\Infrastructure\Storage\Exception\InvalidStreamResourceException;
use Phauthentic\Infrastructure\Storage\Processor\Exception\VariantDoesNotExistException;
use Phauthentic\Infrastructure\Storage\File;
use Phauthentic\Infrastructure\Storage\FileFactory;
use Phauthentic\Infrastructure\Storage\FileInterface;
use Phauthentic\Infrastructure\Storage\PathBuilder\PathBuilder;
use Phauthentic\Infrastructure\Storage\Processor\Exception\VariantDoesNotExistException as ProcessorVariantDoesNotExistException;
use Phauthentic\Infrastructure\Storage\UrlBuilder\LocalUrlBuilder;
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
            ->withMetadataByKey('bar', 'foo');

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
            'metadata' => [
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

    /**
     * @return void
     */
    public function testWithFile(): void
    {
        $fileOnDisk = $this->getFixtureFile('titus.jpg');

        $file = File::create('test.jpg', 1000, 'image/jpeg', 'local')
            ->withFile($fileOnDisk);

        $this->assertIsResource($file->resource());
    }

    /**
     * @return void
     */
    public function testWithResource(): void
    {
        $resource = fopen('php://temp', 'r+');

        $file = File::create('test.jpg', 1000, 'image/jpeg', 'local')
            ->withResource($resource);

        $this->assertSame($resource, $file->resource());
    }

    /**
     * @return void
     */
    public function testWithInvalidResourceThrowsException(): void
    {
        $file = File::create('test.jpg', 1000, 'image/jpeg', 'local');

        $this->expectException(InvalidStreamResourceException::class);

        $file->withResource('not a resource');
    }

    /**
     * @return void
     */
    public function testExtension(): void
    {
        $file = File::create('test.jpg', 1000, 'image/jpeg', 'local');
        $this->assertEquals('jpg', $file->extension());

        $file = File::create('test', 1000, 'image/jpeg', 'local');
        $this->assertNull($file->extension());
    }

    /**
     * @return void
     */
    public function testReadableSize(): void
    {
        // Test bytes
        $file = File::create('test.jpg', 500, 'image/jpeg', 'local');
        $this->assertEquals('500B', $file->readableSize());

        // Test kilobytes
        $file = File::create('test.jpg', 1536, 'image/jpeg', 'local');
        $this->assertEquals('2kB', $file->readableSize());

        // Test megabytes
        $file = File::create('test.jpg', 1048576, 'image/jpeg', 'local');
        $this->assertEquals('1MB', $file->readableSize());

        // Test gigabytes
        $file = File::create('test.jpg', 1073741824, 'image/jpeg', 'local');
        $this->assertEquals('1GB', $file->readableSize());
    }

    /**
     * @return void
     */
    public function testBuildPath(): void
    {
        $pathBuilder = new PathBuilder();

        $file = File::create('test.jpg', 1000, 'image/jpeg', 'local')
            ->withUuid('12345678-1234-1234-1234-123456789012')
            ->buildPath($pathBuilder);

        $this->assertNotNull($file->path());
    }

    /**
     * @return void
     */
    public function testWithMetadataOverwrite(): void
    {
        $file = File::create('test.jpg', 1000, 'image/jpeg', 'local')
            ->withMetadata(['key1' => 'value1'])
            ->withMetadata(['key2' => 'value2'], true);

        $this->assertEquals(['key2' => 'value2'], $file->metadata());
    }

    /**
     * @return void
     */
    public function testVariants(): void
    {
        $file = File::create('test.jpg', 1000, 'image/jpeg', 'local')
            ->withVariant('thumb', ['width' => 100, 'height' => 100]);

        $this->assertTrue($file->hasVariants());
        $this->assertTrue($file->hasVariant('thumb'));
        $this->assertFalse($file->hasVariant('large'));

        $this->assertEquals(['thumb' => ['width' => 100, 'height' => 100]], $file->variants());
        $this->assertEquals(['width' => 100, 'height' => 100], $file->variant('thumb'));
    }

    /**
     * @return void
     */
    public function testVariantDoesNotExistException(): void
    {
        $file = File::create('test.jpg', 1000, 'image/jpeg', 'local');

        $this->expectException(VariantDoesNotExistException::class);

        $file->variant('nonexistent');
    }

    /**
     * @return void
     */
    public function testVariantPaths(): void
    {
        $file = File::create('test.jpg', 1000, 'image/jpeg', 'local')
            ->withVariant('thumb', ['path' => '/path/to/thumb.jpg', 'width' => 100])
            ->withVariant('large', ['width' => 200, 'height' => 200]);

        $paths = $file->variantPaths();

        $this->assertEquals(['thumb' => '/path/to/thumb.jpg'], $paths);
    }

    /**
     * @return void
     */
    public function testWithVariants(): void
    {
        $file = File::create('test.jpg', 1000, 'image/jpeg', 'local')
            ->withVariants([
                'thumb' => ['width' => 100],
                'medium' => ['width' => 300]
            ]);

        $this->assertTrue($file->hasVariant('thumb'));
        $this->assertTrue($file->hasVariant('medium'));

        // Test merge
        $file = $file->withVariants(['large' => ['width' => 500]], false);
        $this->assertFalse($file->hasVariant('thumb'));
        $this->assertTrue($file->hasVariant('large'));
    }

    /**
     * @return void
     */
    public function testUrlMethods(): void
    {
        $urlBuilder = new LocalUrlBuilder('/files');

        $file = File::create('test.jpg', 1000, 'image/jpeg', 'local')
            ->withPath('/uploads/test.jpg')
            ->buildUrl($urlBuilder);

        $this->assertNotEmpty($file->url());

        $file = $file->withUrl('https://example.com/test.jpg');
        $this->assertEquals('https://example.com/test.jpg', $file->url());
    }

    /**
     * @return void
     */
    public function testMetadataKeyMethods(): void
    {
        $file = File::create('test.jpg', 1000, 'image/jpeg', 'local')
            ->withMetadataKey('test_key', 'test_value');

        $this->assertEquals('test_value', $file->metadata()['test_key']);

        $file = $file->withoutMetadataKey('test_key');
        $this->assertArrayNotHasKey('test_key', $file->metadata());
    }

    /**
     * @return void
     */
    public function testBelongsToModel(): void
    {
        $file = File::create('test.jpg', 1000, 'image/jpeg', 'local')
            ->belongsToModel('User', 123);

        $this->assertEquals('User', $file->model());
        $this->assertEquals('123', $file->modelId());
    }

    /**
     * @return void
     */
    public function testAddToCollection(): void
    {
        $file = File::create('test.jpg', 1000, 'image/jpeg', 'local')
            ->addToCollection('avatars');

        $this->assertEquals('avatars', $file->collection());
    }


    /**
     * @return void
     */
    public function testReadableSizeEdgeCases(): void
    {
        // Test zero size file
        $file = File::create('test.jpg', 0, 'image/jpeg', 'local');
        $this->assertEquals('0B', $file->readableSize());

        // Test boundary values around 1024
        $file = File::create('test.jpg', 1023, 'image/jpeg', 'local');
        $this->assertEquals('1023B', $file->readableSize());

        $file = File::create('test.jpg', 1024, 'image/jpeg', 'local');
        $this->assertEquals('1kB', $file->readableSize());

        $file = File::create('test.jpg', 1025, 'image/jpeg', 'local');
        $this->assertEquals('1kB', $file->readableSize());

        // Test boundary values around 1048576 (1MB)
        $file = File::create('test.jpg', 1048575, 'image/jpeg', 'local');
        $this->assertEquals('1024kB', $file->readableSize());

        $file = File::create('test.jpg', 1048576, 'image/jpeg', 'local');
        $this->assertEquals('1MB', $file->readableSize());

        // Test very large file (1TB)
        $file = File::create('test.jpg', 1099511627776, 'image/jpeg', 'local');
        $this->assertEquals('1TB', $file->readableSize());

        // Test edge case with large number
        $file = File::create('test.jpg', PHP_INT_MAX, 'image/jpeg', 'local');
        $size = $file->readableSize();
        $this->assertIsString($size);
        $this->assertGreaterThan(0, strlen($size));
    }

    /**
     * @return void
     */
    public function testWithVariantsMergeComprehensive(): void
    {
        $file = File::create('test.jpg', 1000, 'image/jpeg', 'local')
            ->withVariants([
                'thumb' => ['width' => 100, 'height' => 100, 'path' => 'thumb.jpg'],
                'medium' => ['width' => 300, 'height' => 300, 'path' => 'medium.jpg']
            ]);

        $this->assertTrue($file->hasVariant('thumb'));
        $this->assertTrue($file->hasVariant('medium'));

        // Test merge = true (default) - should merge with existing variants
        $file = $file->withVariants([
            'medium' => ['width' => 350, 'height' => 350, 'path' => 'medium_updated.jpg'], // Update existing
            'large' => ['width' => 800, 'height' => 600, 'path' => 'large.jpg'] // Add new
        ], true);

        $this->assertTrue($file->hasVariant('thumb')); // Should still exist
        $this->assertEquals(['width' => 800, 'height' => 600, 'path' => 'large.jpg'], $file->variant('large')); // New variant
        // array_merge_recursive merges arrays, so medium will have arrays for each property
        $mediumVariant = $file->variant('medium');
        $this->assertContains(350, $mediumVariant['width']);
        $this->assertContains(300, $mediumVariant['width']); // Original value
        $this->assertContains('medium_updated.jpg', $mediumVariant['path']);
        $this->assertContains('medium.jpg', $mediumVariant['path']); // Original value
    }

    /**
     * @return void
     */
    public function testVariantPathsWithEmptyPaths(): void
    {
        $file = File::create('test.jpg', 1000, 'image/jpeg', 'local')
            ->withVariant('thumb', ['path' => 'thumb.jpg', 'width' => 100])
            ->withVariant('medium', ['width' => 300, 'height' => 300]) // No path
            ->withVariant('large', ['path' => '', 'width' => 800]) // Empty path
            ->withVariant('xlarge', ['path' => 'xlarge.jpg', 'width' => 1200]);

        $paths = $file->variantPaths();

        $expected = [
            'thumb' => 'thumb.jpg',
            'xlarge' => 'xlarge.jpg'
            // Empty paths are excluded
        ];

        $this->assertEquals($expected, $paths);
    }

    /**
     * @return void
     */
    public function testJsonSerialize(): void
    {
        $file = File::create('test.jpg', 1000, 'image/jpeg', 'local')
            ->withUuid('12345678-1234-1234-1234-123456789012')
            ->belongsToModel('User', 1)
            ->addToCollection('avatars')
            ->withPath('/uploads/test.jpg')
            ->withMetadata(['key' => 'value'])
            ->withVariant('thumb', ['width' => 100, 'path' => 'thumb.jpg']);

        $serialized = $file->jsonSerialize();
        $expectedKeys = [
            'uuid', 'filename', 'filesize', 'mimeType', 'extension',
            'path', 'model', 'modelId', 'collection', 'readableSize',
            'variants', 'metadata', 'url'
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $serialized);
        }

        $this->assertEquals('12345678-1234-1234-1234-123456789012', $serialized['uuid']);
        $this->assertEquals('test.jpg', $serialized['filename']);
        $this->assertEquals(1000, $serialized['filesize']);
        $this->assertEquals('image/jpeg', $serialized['mimeType']);
        $this->assertEquals('jpg', $serialized['extension']);
        $this->assertEquals('/uploads/test.jpg', $serialized['path']);
        $this->assertEquals('User', $serialized['model']);
        $this->assertEquals('1', $serialized['modelId']);
        $this->assertEquals('avatars', $serialized['collection']);
        $this->assertEquals('1000B', $serialized['readableSize']);
        $this->assertEquals(['key' => 'value'], $serialized['metadata']);
        $this->assertArrayHasKey('thumb', $serialized['variants']);
        $this->assertEquals('', $serialized['url']);
    }
}
