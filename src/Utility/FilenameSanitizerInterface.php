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

namespace Phauthentic\Infrastructure\Storage\Utility;

/**
 * FilenameSanitizerInterface
 */
interface FilenameSanitizerInterface
{
    /**
     * @param string $filename Filename
     * @return string
     */
    public function sanitize(string $filename): string;

    /**
     * Beautifies a filename to make it better to read
     *
     * "file   name.zip" becomes "file-name.zip"
     * "file___name.zip" becomes "file-name.zip"
     * "file---name.zip" becomes "file-name.zip"
     * "file--.--.-.--name.zip" becomes "file.name.zip"
     * "file...name..zip" becomes "file.name.zip"
     * ".file-name.-" becomes "file-name"
     *
     * @link https://stackoverflow.com/questions/2021624/string-sanitizer-for-filename
     * @param string $filename Filename
     * @return string
     */
    public function beautify(string $filename): string;
}
