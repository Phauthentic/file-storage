<?php

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
