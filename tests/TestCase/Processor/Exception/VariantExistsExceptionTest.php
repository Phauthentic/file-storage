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

namespace Phauthentic\Test\TestCase\Processor\Image\Exception;

use Phauthentic\Infrastructure\Storage\Processor\Exception\VariantExistsException;
use Phauthentic\Test\TestCase\TestCase;

/**
 * ManipulationExistsExceptionTest
 */
class VariantExistsExceptionTest extends TestCase
{
    /**
     * @return void
     */
    public function testException(): void
    {
        $exception = VariantExistsException::withName('test');
        $expected = 'A variant with the name `test` already exists';
        $this->assertEquals($expected, $exception->getMessage());
    }
}
