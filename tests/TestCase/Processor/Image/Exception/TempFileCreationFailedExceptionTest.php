<?php

declare(strict_types=1);

namespace Phauthentic\Test\TestCase\Processor\Image\Exception;

use Phauthentic\Infrastructure\Storage\Processor\Image\Exception\TempFileCreationFailedException;
use Phauthentic\Test\TestCase\TestCase;

/**
 * TempFileCreationFailedExceptionTest
 */
class TempFileCreationFailedExceptionTest extends TestCase
{
    /**
     * @return void
     */
    public function testException(): void
    {
        $exception = TempFileCreationFailedException::withFilename('/tmp/titus.jpg');
        $this->assertEquals(
            'Failed to create `/tmp/titus.jpg`',
            $exception->getMessage()
        );
    }
}
