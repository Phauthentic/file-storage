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

namespace Phauthentic\Infrastructure\Storage\PathBuilder;

use InvalidArgumentException;
use Phauthentic\Infrastructure\Storage\FileInterface;
use Phauthentic\Infrastructure\Storage\Utility\FilenameSanitizer;
use Phauthentic\Infrastructure\Storage\Utility\FilenameSanitizerInterface;
use Phauthentic\Infrastructure\Storage\Utility\PathInfo;

/**
 * A path builder is an utility class that generates a path and filename for a
 * file storage entity. All the fields from the entity can bed used to create
 * a path and file name.
 */
class PathBuilder implements PathBuilderInterface
{
    /**
     * Default settings.
     *
     * @var array
     */
    protected array $defaultConfig = [
        'stripUuid' => true,
        'randomPath' => 'sha1',
        'sanitizeFilename' => true,
        'beautifyFilename' => false,
        'sanitizer' => null,
        'pathTemplate' => '{model}{ds}{randomPath}{ds}{id}',
        'filenameTemplate' => '{filename}.{extension}',
        'manipulationTemplate' => '{filename}.{manipulation}.{extension}'
    ];

    /**
     * @var array
     */
    protected array $config = [];

    /**
     * @var \Phauthentic\Infrastructure\Storage\Utility\FilenameSanitizerInterface
     */
    protected FilenameSanitizerInterface $filenameSanitizer;

    /**
     * Constructor
     *
     * @param array $config Configuration options.
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge_recursive($this->defaultConfig, $config);

        if (!$this->config['sanitizer'] instanceof FilenameSanitizerInterface) {
            $this->filenameSanitizer = new FilenameSanitizer();
        }
    }

    /**
     * Strips dashes from a string
     *
     * @param string $uuid UUID as string
     * @return string String without the dashed
     */
    protected function stripDashes(string $uuid): string
    {
        return str_replace('-', '', $uuid);
    }

    /**
     * Builds the path under which the data gets stored in the storage adapter.
     *
     * @param \Phauthentic\Infrastructure\Storage\FileInterface $file
     * @param array $options Options
     * @return string
     */
    public function path(FileInterface $file, array $options = []): string
    {
        $config = array_merge($this->config, $options);

        $path = str_replace(
            ['{ds}', '{model}', '{collection}', '{id}', '{randomPath}', '{modelId}'],
            [DIRECTORY_SEPARATOR, $file->model(), $file->collection(), $this->stripDashes($file->uuid()), $this->randomPath($file->uuid()), $file->modelId()],
            $config['pathTemplate']
        );

        $path = $this->ensureSlash($path, 'after');
        $path .= $this->filename($file, $config);

        return $path;
    }

    /**
     * Builds the filename of under which the data gets saved in the storage adapter.
     *
     * @param \Phauthentic\Infrastructure\Storage\FileInterface $file
     * @param array $options Options
     * @return string
     */
    public function filename(FileInterface $file, array $options = []): string
    {
        $config = array_merge($this->config, $options);
        $pathInfo = PathInfo::for($file->filename());
        $filename = $pathInfo->filename();

        if ($config['sanitizeFilename'] === true) {
            $filename = $this->filenameSanitizer->sanitize($pathInfo->filename());
        }

        if ($config['beautifyFilename'] === true) {
            $filename = $this->filenameSanitizer->beautify($pathInfo->filename());
        }

        $filename = str_replace(
            ['{model}', '{collection}', '{id}', '{modelId}', '{filename}', '{extension}'],
            [$file->model(), $file->collection(), $this->stripDashes($file->uuid()), $file->modelId(), $filename, $pathInfo->extension()],
            $config['filenameTemplate']
        );

        return $filename;
    }

    /**
     * Creates a semi-random path based on a string.
     *
     * Makes it possible to overload this functionality.
     *
     * @param string $string Input string
     * @param int $level Depth of the path to generate.
     * @param string $method Hash method, crc32 or sha1.
     * @throws \InvalidArgumentException
     * @return string
     */
    protected function randomPath($string, $level = 3, $method = 'sha1'): string
    {
        if ($method === 'sha1') {
            return $this->randomPathSha1($string, $level);
        }

        if (is_callable($method)) {
            return $method($string, $level);
        }

        throw new InvalidArgumentException(sprintf(
            'BasepathBuilder::randomPath() invalid hash `%s` method provided!',
            $method
        ));
    }

    /**
     * Creates a semi-random path based on a string.
     *
     * Makes it possible to overload this functionality.
     *
     * @param string $string Input string
     * @param int $level Depth of the path to generate.
     * @return string
     */
    protected function randomPathSha1(string $string, int $level): string
    {
        $result = sha1($string);
        $randomString = '';
        $counter = 0;
        for ($i = 1; $i <= $level; $i++) {
            $counter += 2;
            $randomString .= substr($result, $counter, 2) . DIRECTORY_SEPARATOR;
        }

        return substr($randomString, 0, -1);
    }

    /**
     * Ensures that a path has a leading and/or trailing (back-) slash.
     *
     * @param string $string
     * @param string $position Can be `before`, `after` or `both`
     * @param string|null $ds Directory separator should be / or \, if not set the DIRECTORY_SEPARATOR constant is used.
     * @throws \InvalidArgumentException
     * @return string
     */
    protected function ensureSlash(string $string, string $position, ?string $ds = null): string
    {
        if (!in_array($position, ['before', 'after', 'both'])) {
            $method = get_class($this) . '::ensureSlash(): ';
            throw new InvalidArgumentException(sprintf(
                $method . 'Invalid position `%s`!',
                $position
            ));
        }

        if ($ds === null) {
            $ds = DIRECTORY_SEPARATOR;
        }

        if ($position === 'before' || $position === 'both') {
            if (strpos($string, $ds) !== 0) {
                $string = $ds . $string;
            }
        }

        if ($position === 'after' || $position === 'both') {
            if ($string[strlen($string) - 1] !== $ds) {
                $string .= $ds;
            }
        }

        return $string;
    }

    /**
     * @inheritDoc
     */
    public function pathForManipulation(FileInterface $file, string $name, array $options = []): string
    {
        $hash = substr(hash('sha1', $name), 0, 6);
        $path = $this->path($file);
        $pathInfo = PathInfo::for($path);
        $ds = DIRECTORY_SEPARATOR;

        $path = $pathInfo->dirname() . $ds . $pathInfo->filename();
        if ($pathInfo->hasExtension()) {
            return $path . '.' . $hash . '.' . $pathInfo->extension();
        }

        return $path . $pathInfo->extension();
    }
}
