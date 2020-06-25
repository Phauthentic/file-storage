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

use Phauthentic\Infrastructure\Storage\Exception\FileDoesNotExistException;
use Phauthentic\Test\TestCase\TestCase;

/**
 * FileDoesNotExistExceptionTest
 */
class FileDoesNotExistExceptionTest extends TestCase
{
    /**
     * @return void
     */
    public function testException(): void
    {
        $exception = FileDoesNotExistException::filename('foobar.jpg');
        $this->assertEquals(
            'File foobar.jpg does not exist',
            $exception->getMessage()
        );
    }
}
