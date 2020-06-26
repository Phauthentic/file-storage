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

namespace Phauthentic\Test\TestCase\Processor\Image;

use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Phauthentic\Infrastructure\Storage\FileFactory;
use Phauthentic\Infrastructure\Storage\FileStorageInterface;
use Phauthentic\Infrastructure\Storage\PathBuilder\PathBuilder;
use Phauthentic\Infrastructure\Storage\PathBuilder\PathBuilderInterface;
use Phauthentic\Infrastructure\Storage\Processor\Image\ImageVariantCollection;
use Phauthentic\Infrastructure\Storage\Processor\Image\ImageProcessor;
use Phauthentic\Test\TestCase\TestCase;

/**
 * ImageProcessorTest
 */
class ImageProcessorTest extends TestCase
{
    /**
     * @return void
     */
    public function testProcessor(): void
    {
        $fileStorage = $this->getMockBuilder(FileStorageInterface::class)
            ->getMock();

        $pathBuilder = new PathBuilder();

        $imageManager = $this->getMockBuilder(ImageManager::class)
            ->getMock();

        $image = $this->getMockBuilder(Image::class)
            ->getMock();

        $imageManager->expects($this->any())
            ->method('make')
            ->willReturn($image);

        $processor = new ImageProcessor(
            $fileStorage,
            $pathBuilder,
            $imageManager
        );

        $fileOnDisk = $this->getFixtureFile('titus.jpg');

        $file = FileFactory::fromDisk($fileOnDisk, 'local')
            ->withUuid('914e1512-9153-4253-a81e-7ee2edc1d973')
            ->withFilename('foobar.jpg')
            ->addToCollection('avatar')
            ->belongsToModel('User', '1');

        $collection = ImageVariantCollection::create();
        $collection
            ->addNew('resizeAndFlip')
            ->flipHorizontal()
            ->resize(300, 300)
            ->optimize();

        $file = $file->withVariants($collection->toArray());

        $file = $processor->process($file);
    }
}
