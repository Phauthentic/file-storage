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

use Phauthentic\Infrastructure\Storage\Utility\NoopFilenameSanitizer;
use Phauthentic\Test\TestCase\TestCase;

/**
 * NoopFilenameSanitizerTest
 */
class NoopFilenameSanitizerTest extends TestCase
{
    /**
     * @return void
     */
    public function testSanitizer(): void
    {
        $sanitizer = new NoopFilenameSanitizer();

        // Valid name without extension
        $result = $sanitizer->sanitize('this # "", \` - ! should-no-change');
        $this->assertEquals('this # "", \` - ! should-no-change', $result);
    }

    /**
     * @return void
     */
    public function testBeautify(): void
    {
        $sanitizer = new NoopFilenameSanitizer();

        $result = $sanitizer->beautify('file--.--.-.--name.zip');
        $this->assertEquals('file--.--.-.--name.zip', $result);
    }
}
