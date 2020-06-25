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

use Phauthentic\Infrastructure\Storage\Processor\Manipulation;
use Phauthentic\Test\TestCase\TestCase;

/**
 * ManipulationTest
 */
class ManipulationTest extends TestCase
{
    /**
     * @return void
     */
    public function testManipulation(): void
    {
        $manipulation = (new class() extends Manipulation {
            protected string $name = 'test';
        });

        $this->assertEquals('test', $manipulation->name());
        $this->assertEquals('', $manipulation->path());

        $manipulation = $manipulation->withPath('/');
        $this->assertEquals('/', $manipulation->path());
        $this->assertFalse($manipulation->hasOperations());

        $expected = [
            'operations' => [],
            'path' => '/',
            'url' => '',
            'name' => 'test'
        ];
        $result = $manipulation->toArray();

        $this->assertEquals($expected, $result);
    }
}
