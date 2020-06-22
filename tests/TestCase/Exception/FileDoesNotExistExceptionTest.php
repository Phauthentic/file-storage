<?php

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
