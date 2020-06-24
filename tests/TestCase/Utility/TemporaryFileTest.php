<?php

declare(strict_types=1);

namespace Phauthentic\Test\TestCase\Utility;

use Phauthentic\Infrastructure\Storage\Utility\TemporaryFile;
use Phauthentic\Test\TestCase\TestCase;

/**
 * TemporaryFileTest
 */
class TemporaryFileTest extends TestCase
{
    /**
     * @return void
     */
    public function testTemporaryFile(): void
    {
        $result = TemporaryFile::create();
        $this->assertFileExists($result);
        unlink($result);

        $result = TemporaryFile::tempDir();
        $this->assertDirectoryExists($result);

        $ds = DIRECTORY_SEPARATOR;
        $previousTmpFolder = TemporaryFile::tempDir();
        $tmpFolder = (__DIR__ . $ds . '..' . $ds . '..' . $ds . '..' . $ds . 'tmp');
        TemporaryFile::setTempFolder($tmpFolder);

        $result = TemporaryFile::create();
        $this->assertFileExists($result);
        unlink($result);
        TemporaryFile::setTempFolder($previousTmpFolder);
    }
}
