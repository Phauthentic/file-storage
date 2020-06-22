<?php

declare(strict_types=1);

namespace Phauthentic\Test\TestCase\Utility;

use Phauthentic\Infrastructure\Storage\Utility\FilenameSanitizer;
use Phauthentic\Test\TestCase\TestCase;

/**
 * FilenameSanitizerTest
 */
class FilenameSanitizerTest extends TestCase
{
    /**
     * @return void
     */
    public function testSanitizer(): void
    {
        $sanitizer = new FilenameSanitizer();

        // Valid name without extension
        $result = $sanitizer->sanitize('this-should-be-valid');
        $this->assertEquals('this-should-be-valid', $result);

        // Valid name with extension
        $result = $sanitizer->sanitize('this-should-be-valid.ext');
        $this->assertEquals('this-should-be-valid.ext', $result);
    }

    /**
     * @return void
     */
    public function testBeautify(): void
    {
        $sanitizer = new FilenameSanitizer();

        $result = $sanitizer->beautify('file   name.zip');
        $this->assertEquals('file-name.zip', $result);

        $result = $sanitizer->beautify('file___name.zip');
        $this->assertEquals('file-name.zip', $result);

        $result = $sanitizer->beautify('file---name.zip');
        $this->assertEquals('file-name.zip', $result);

        $result = $sanitizer->beautify('file--.--.-.--name.zip');
        $this->assertEquals('file.name.zip', $result);

        $result = $sanitizer->beautify('file...name..zip');
        $this->assertEquals('file.name.zip', $result);
    }
}
