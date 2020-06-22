<?php

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
