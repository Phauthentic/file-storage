<?php

declare(strict_types=1);

namespace Phauthentic\Test\TestCase\Processor\Image;

use ArrayIterator;
use Phauthentic\Infrastructure\Storage\Processor\Image\ImageManipulationCollection;
use Phauthentic\Test\TestCase\TestCase;

/**
 * Image Manipulation Collection
 */
class ImageManipulationCollectionTest extends TestCase
{
    /**
     * @return void
     */
    public function testFile(): void
    {
        $collection = ImageManipulationCollection::create();
        $collection
            ->addNew('resizeAndFlip')
            ->flipHorizontal()
            ->resize(300, 300)
            ->optimize();

        $this->assertTrue($collection->has('resizeAndFlip'));
        $this->assertFalse($collection->has('does-not-exist'));

        $result = $collection->get('resizeAndFlip');
        $this->assertEquals('resizeAndFlip', $result->name());

        $result = $collection->toArray();
        $this->assertNotEmpty($result);

        $this->assertInstanceOf(ArrayIterator::class, $collection->getIterator());

        $expected = [
            'resizeAndFlip' => [
                'operations' => [
                    'flip' => [
                        'direction' => 'h',
                    ],
                    'resize' => [
                        'width' => 300,
                        'height' => 300,
                        'aspectRatio' => true,
                        'preventUpscale' => false,
                    ],
                ],
                'path' => '',
                'optimize' => true,
                'url' => ''
            ],
        ];

        $this->assertEquals($expected, $collection->toArray());

        $collection2 = ImageManipulationCollection::fromArray($expected);
        $this->assertEquals($expected, $collection2->toArray());

        $collection2->remove('resizeAndFlip');
        $this->assertFalse($collection2->has('resizeAndFlip'));
    }
}
