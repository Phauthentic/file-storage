<?php

declare(strict_types=1);

namespace Phauthentic\Test\TestCase\Utility;

use Phauthentic\Infrastructure\Storage\Utility\PathInfo;
use Phauthentic\Test\TestCase\TestCase;

/**
 * PathInfoTest
 */
class PathInfoTest extends TestCase
{
    /**
     * @return void
     */
    public function testPathInfo(): void
    {
        $result = PathInfo::for('/some/nice/file.jpg');

        $this->assertEquals('jpg', $result->extension());
        $this->assertEquals('file', $result->filename());
        $this->assertEquals('/some/nice', $result->dirname());
        $this->assertEquals('file.jpg', $result->basename());
        $this->assertTrue($result->hasExtension());
    }
}
