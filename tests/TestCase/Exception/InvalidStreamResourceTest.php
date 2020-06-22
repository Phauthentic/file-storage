<?php

declare(strict_types=1);

namespace Phauthentic\Test\TestCase\Exception;

use Phauthentic\Infrastructure\Storage\Exception\InvalidStreamResource;
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
        $exception = InvalidStreamResource::create();
        $this->assertEquals(
            'The provided value is not a valid stream resource',
            $exception->getMessage()
        );
    }
}
