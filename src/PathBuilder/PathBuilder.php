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
        'directorySeparator' => DIRECTORY_SEPARATOR,
        'randomPath' => 'sha1',
        'sanitizeFilename' => true,
        'beautifyFilename' => false,
        'sanitizer' => null,
        'pathTemplate' => '{model}{ds}{randomPath}{ds}{strippedId}{ds}{filename}.{extension}',
        'manipulationPathTemplate' => '{model}{ds}{randomPath}{ds}{strippedId}{ds}{filename}.{hashedManipulation}.{extension}'
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
     * @param \Phauthentic\Infrastructure\Storage\FileInterface $file
     * @param array $options Options
     * @return string
     */
    protected function filename(FileInterface $file, array $options = []): string
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

        return $filename;
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
        $ds = $this->config['directorySeparator'];
        $filename = $this->filename($file, $options);

        $placeholders = [
            '{ds}' => $ds,
            '{model}' => $file->model(),
            '{collection}' => $file->collection(),
            '{id}' => $file->uuid(),
            '{randomPath}' => $this->randomPath($file->uuid()),
            '{modelId}' => $file->modelId(),
            '{strippedId}' => $this->stripDashes($file->uuid()),
            '{extension}' => $file->extension(),
            '{mimeType}' => $file->mimeType(),
            '{filename}' => $filename,
            '{hashedFilename}' => sha1($filename),
        ];

        return $this->parseTemplate($placeholders, $config['pathTemplate'], $ds);
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
     * @inheritDoc
     */
    public function pathForManipulation(FileInterface $file, string $manipulation, array $options = []): string
    {
        $config = array_merge($this->config, $options);
        $ds = $this->config['directorySeparator'];
        $filename = $this->filename($file, $options);
        $hashedManipulation = substr(hash('sha1', $manipulation), 0, 6);

        $placeholders = [
            '{ds}' => $ds,
            '{model}' => $file->model(),
            '{collection}' => $file->collection(),
            '{id}' => $file->uuid(),
            '{randomPath}' => $this->randomPath($file->uuid()),
            '{modelId}' => $file->modelId(),
            '{strippedId}' => $this->stripDashes($file->uuid()),
            '{extension}' => $file->extension(),
            '{mimeType}' => $file->mimeType(),
            '{filename}' => $filename,
            '{hashedFilename}' => sha1($filename),
            '{manipulation}' => $manipulation,
            '{hashedManipulation}' => $hashedManipulation
        ];

        $result = $this->parseTemplate($placeholders, $config['manipulationPathTemplate'], $ds);

        $pathInfo = PathInfo::for($result);
        if (!$pathInfo->hasExtension() && substr($result,  -1) === '.') {
            return substr($result, 0, -1);
        }

        return $result;
    }

    /**
     * Parses the path string template
     *
     * @param array $placeholders Assoc array of placeholder to value
     * @param string $template Template string
     * @param string $separator Directory Separator
     * @return string
     */
    protected function parseTemplate(
        array $placeholders,
        string $template,
        string $separator
    ): string {
        $result = str_replace(
            array_keys($placeholders),
            array_values($placeholders),
            $template
        );

        // Remove double or more separators caused by empty template vars
        return  preg_replace('/(\\\{2,})|(\/{2,})/', $separator, $result);
    }
}
