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

use Phauthentic\Test\TestCase\TestCase;
use RuntimeException;

/**
 * Functions Test
 */
class FunctionsTest extends TestCase
{
    /**
     * @return void
     */
    public function testFopenSuccessWithContext(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tempFile, 'test content');

        $context = stream_context_create(['http' => ['method' => 'GET']]);
        $result = \Phauthentic\Infrastructure\Storage\fopen($tempFile, 'r', true, $context);

        $this->assertIsResource($result);
        $this->assertEquals('stream', get_resource_type($result));

        fclose($result);
        unlink($tempFile);
    }

    /**
     * @return void
     */
    public function testFopenSuccessWithoutContext(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tempFile, 'test content');

        $result = \Phauthentic\Infrastructure\Storage\fopen($tempFile, 'r');

        $this->assertIsResource($result);
        $this->assertEquals('stream', get_resource_type($result));

        fclose($result);
        unlink($tempFile);
    }


    /**
     * @return void
     */
    public function testFopenWithDifferentModes(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tempFile, 'test content');

        // Test different modes
        $modes = ['r', 'rb', 'w', 'wb', 'a', 'ab'];

        foreach ($modes as $mode) {
            $result = \Phauthentic\Infrastructure\Storage\fopen($tempFile, $mode);
            $this->assertIsResource($result);
            $this->assertEquals('stream', get_resource_type($result));
            fclose($result);
        }

        unlink($tempFile);
    }
}
