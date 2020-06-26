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

        // Lowercase all chars
        $sanitizer = new FilenameSanitizer([
            'lowercase' => true
        ]);

        $result = $sanitizer->sanitize('MAKE-ME-LOWER-CASE');
        $this->assertEquals('make-me-lower-case', $result);

        // Remove all non alpha numeric chars
        $sanitizer = new FilenameSanitizer([
            'removeAllNonAlphaNumerical' => true
        ]);

        $result = $sanitizer->sanitize('Remove + this!.txt');
        $this->assertEquals('removethis.txt', $result);

        // Max length enforcement
        $sanitizer = new FilenameSanitizer([
            'enforceMaxLength' => true,
            'maxLength' => 10
        ]);

        $result = $sanitizer->sanitize('this-is-longer-than-tne-chars.txt');
        $this->assertEquals('this-i.txt', $result);
        $this->assertEquals(10, strlen($result));
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
