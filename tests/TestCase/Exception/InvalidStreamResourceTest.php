<?php

declare(strict_types=1);

namespace Phauthentic\Test\TestCase\Exception;

use Phauthentic\Infrastructure\Storage\Exception\InvalidStreamResourceException;
use Phauthentic\Test\TestCase\TestCase;

/**
 * InvalidStreamResourceTest
 */
class InvalidStreamResourceTest extends TestCase
{
    /**
     * @return void
     */
    public function testException(): void
    {
        $exception = InvalidStreamResourceException::create();
        $this->assertEquals(
            'The provided value is not a valid stream resource',
            $exception->getMessage()
        );
    }
}
