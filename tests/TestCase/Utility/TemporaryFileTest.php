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
