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

use Phauthentic\Infrastructure\Storage\Processor\Image\Exception\UnsupportedOperationException;
use Phauthentic\Test\TestCase\TestCase;

/**
 * UnsupportedOperationExceptionTest
 */
class UnsupportedOperationExceptionTest extends TestCase
{
    /**
     * @return void
     */
    public function testException(): void
    {
        $exception = UnsupportedOperationException::withName('resize');
        $this->assertEquals(
            'Operation `resize` is not implemented or supported',
            $exception->getMessage()
        );
    }
}
