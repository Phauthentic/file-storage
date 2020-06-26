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

namespace Phauthentic\Test\TestCase\Processor;

use Phauthentic\Infrastructure\Storage\Processor\Variant;
use Phauthentic\Test\TestCase\TestCase;

/**
 * VariantTest
 */
class VariantTest extends TestCase
{
    /**
     * @return void
     */
    public function testVariant(): void
    {
        $variant = (new class () extends Variant {
            protected string $name = 'test';
        });

        $this->assertEquals('test', $variant->name());
        $this->assertEquals('', $variant->path());

        $variant = $variant->withPath('/');
        $this->assertEquals('/', $variant->path());
        $this->assertFalse($variant->hasOperations());

        $expected = [
            'operations' => [],
            'path' => '/',
            'url' => '',
            'name' => 'test'
        ];
        $result = $variant->toArray();

        $this->assertEquals($expected, $result);
    }
}
