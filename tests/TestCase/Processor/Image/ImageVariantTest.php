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

use Phauthentic\Infrastructure\Storage\Processor\Image\ImageVariant;
use Phauthentic\Test\TestCase\TestCase;

/**
 * ImageVariantTest
 */
class ImageVariantTest extends TestCase
{
    /**
     * @return void
     */
    public function testVariant(): void
    {
        $variant = ImageVariant::create('resize')
            ->resize(200, 200)
            ->flipHorizontal()
            ->flipVertical()
            ->flip(ImageVariant::FLIP_VERTICAL)
            ->optimize();

        $this->assertEquals('resize', $variant->name());
        $this->assertEquals('', $variant->path());

        $variant = $variant->withPath('/');
        $this->assertEquals('/', $variant->path());
        $this->assertTrue($variant->hasOperations());

        $expected = [
            'operations' => [
                'resize' => [
                    'width' => 200,
                    'height' => 200,
                    'aspectRatio' => true,
                    'preventUpscale' => false
                ],
                'flipHorizontal' => [
                    'direction' => 'h'
                ],
                'flipVertical' => [
                    'direction' => 'v'
                ],
                'flip' => [
                    'direction' => 'v'
                ]
            ],
            'path' => '/',
            'url' => '',
            'optimize' => true
        ];
        $result = $variant->toArray();

        $this->assertEquals($expected, $result);
    }
}
