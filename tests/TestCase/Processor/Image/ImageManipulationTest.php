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

use Phauthentic\Infrastructure\Storage\Processor\Image\ImageManipulation;
use Phauthentic\Test\TestCase\TestCase;

/**
 * Image Manipulation Collection
 */
class ImageManipulationTest extends TestCase
{
    /**
     * @return void
     */
    public function testManipulation(): void
    {
        $manipulation = ImageManipulation::create('resize')
            ->resize(200, 200)
            ->flipHorizontal()
            ->flipVertical()
            ->flip(ImageManipulation::FLIP_VERTICAL)
            ->optimize();

        $this->assertEquals('resize', $manipulation->name());
        $this->assertEquals('', $manipulation->path());

        $manipulation = $manipulation->withPath('/');
        $this->assertEquals('/', $manipulation->path());
        $this->assertTrue($manipulation->hasOperations());

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
        $result = $manipulation->toArray();

        $this->assertEquals($expected, $result);
    }
}
