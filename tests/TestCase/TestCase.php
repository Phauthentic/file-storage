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

namespace Phauthentic\Test\TestCase;

use PHPUnit\Framework\TestCase as PhpUnitTestCase;

/**
 * Test Case
 */
class TestCase extends PhpUnitTestCase
{
    /**
     * @var string
     */
    protected string $storageRoot = '';

    /**
     * @var string
     */
    protected string $fixtureRoot = '';

    /**
     * @inheritDoc
     */
    public function setup(): void
    {
        parent::setup();

        $ds = DIRECTORY_SEPARATOR;
        $this->storageRoot = __DIR__ . $ds . '..' . $ds . '..' . $ds . 'tmp' . $ds;
        $this->fixtureRoot = __DIR__ . $ds . '..' . $ds . 'Fixtures' . $ds;
    }

    public function tearDown(): void
    {
        $this->cleanUpFiles();
    }

    /**
     * @return void
     */
    public function cleanUpFiles(): void
    {
        if (is_dir($this->storageRoot . 'storage1')) {
            $this->rrmdir($this->storageRoot . 'storage1');
        }
        if (is_dir($this->storageRoot . 'storage2')) {
            $this->rrmdir($this->storageRoot . 'storage2');
        }
    }

    /**
     * @param string $path Path
     * @return string
     */
    public function getFixtureFile($path): string
    {
        $ds = DIRECTORY_SEPARATOR;

        return __DIR__ . $ds . '..' . $ds . 'Fixtures' . $ds . $path;
    }

    /**
     * @param string $path Path
     * @return void
     */
    protected function rrmdir(string $path): void
    {
        if (is_dir($path)) {
            $objects = scandir($path);
            foreach ($objects as $object) {
                if ($object !== "." && $object !== "..") {
                    if (is_dir($path . DIRECTORY_SEPARATOR . $object) && !is_link($path . '/' . $object)) {
                        $this->rrmdir($path . DIRECTORY_SEPARATOR . $object);
                    } else {
                        unlink($path . DIRECTORY_SEPARATOR . $object);
                    }
                }
            }
            rmdir($path);
        }
    }

    public function sanitizeSeparator($string)
    {
        if (DIRECTORY_SEPARATOR === '/') {
            return str_replace('\\', '/', $string);
        }

        return str_replace('/', '\\', $string);
    }
}
