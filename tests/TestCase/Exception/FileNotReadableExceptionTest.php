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

namespace Phauthentic\Test\TestCase\Exception;

use Phauthentic\Infrastructure\Storage\Exception\FileNotReadableException;
use Phauthentic\Test\TestCase\TestCase;

/**
 * FileNotReadableExceptionTest
 */
class FileNotReadableExceptionTest extends TestCase
{
    /**
     * @return void
     */
    public function testException(): void
    {
        $exception = FileNotReadableException::filename('foobar.jpg');
        $this->assertEquals(
            'File foobar.jpg is not readable',
            $exception->getMessage()
        );
    }
}
