<?php

declare(strict_types=1);

namespace Phauthentic\Test\TestCase;

use Phauthentic\Infrastructure\Storage\FileFactory;

/**
 * File Test
 */
class FileTest extends TestCase
{
    /**
     * @return void
     */
    public function testFile(): void
    {
        $fileOnDisk = $this->getFixtureFile('titus.jpg');

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
    }
}
