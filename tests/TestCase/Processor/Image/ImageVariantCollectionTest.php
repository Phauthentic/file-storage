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

use ArrayIterator;
use Phauthentic\Infrastructure\Storage\Processor\Image\ImageVariant;
use Phauthentic\Infrastructure\Storage\Processor\Image\ImageVariantCollection;
use Phauthentic\Test\TestCase\TestCase;

/**
 * ImageVariantCollectionTest
 */
class ImageVariantCollectionTest extends TestCase
{
    /**
     * @return void
     */
    public function testCollectionSetAndGet(): void
    {
        $collection = ImageVariantCollection::create();
        $this->assertCount(0, $collection);

        $collection
            ->addNew('flipHorizontal')
            ->flipHorizontal()
            ->optimize();

        $this->assertCount(1, $collection);
        $this->assertInstanceOf(ImageVariant::class, $collection->get('flipHorizontal'));
    }

    /**
     * @return void
     */
    public function testCollection(): void
    {
        $collection = ImageVariantCollection::create();
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
                    'flipHorizontal' => [
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

        $collection2 = ImageVariantCollection::fromArray($expected);
        $this->assertEquals($expected, $collection2->toArray());

        $this->assertTrue($collection2->has('resizeAndFlip'));
        $collection2->remove('resizeAndFlip');
        $this->assertFalse($collection2->has('resizeAndFlip'));

        $expected = '{"resizeAndFlip":{"operations":{"flipHorizontal":{"direction":"h"},"resize":{"width":300,"height":300,"aspectRatio":true,"preventUpscale":false}},"path":"","url":"","optimize":true}}';
        $result = json_encode($collection);
        $this->assertEquals($expected, $result);
    }
}
