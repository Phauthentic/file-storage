<?php

declare(strict_types=1);

namespace Phauthentic\Test\TestCase\PathBuilder;

use Phauthentic\Infrastructure\Storage\FileFactory;
use Phauthentic\Infrastructure\Storage\PathBuilder\PathBuilder;
use Phauthentic\Infrastructure\Storage\Processor\Image\ImageManipulationCollection;
use Phauthentic\Test\TestCase\TestCase;

/**
 * PathBuilderTest
 */
class PathBuilderTest extends TestCase
{
    /**
     * @return void
     */
    public function testBuilder(): void
    {
        $file = $this->getFixtureFile('titus.jpg');

        $collection = ImageManipulationCollection::create();
        $collection
            ->addNew('resizeAndFlip')
            ->flipHorizontal()
            ->resize(300, 300)
            ->optimize();

        $file = FileFactory::fromDisk($file, 'local')
            ->withUuid('914e1512-9153-4253-a81e-7ee2edc1d973')
            ->addToCollection('avatar')
            ->belongsToModel('User', '1')
            ->withManipulations($collection->toArray());

        $builder = new PathBuilder();

        $result = $builder->path($file);
        $this->assertEquals(
            $this->sanitizeSeparator('User\fe\c3\b4\914e151291534253a81e7ee2edc1d973\titus.jpg'),
            $result
        );

        $result = $builder->pathForManipulation($file, 'resizeAndFlip');
        $this->assertEquals(
            $this->sanitizeSeparator('User\fe\c3\b4\914e151291534253a81e7ee2edc1d973\titus.7ae239.jpg'),
            $result
        );
    }
}
