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

use Psr\Http\Message\StreamInterface;
use InvalidArgumentException;

/**
 * Converts PSR7 streams into PHP stream resources.
 *
 * @see https://www.php.net/streamwrapper
 */
class Psr7StreamWrapper
{
    /** @var resource */
    public $context;

    /** @var \Psr\Http\Message\StreamInterface */
    private StreamInterface $stream;

    /** @var string r, r+, or w */
    private string $mode;

    /**
     * Returns a resource representing the stream.
     *
     * @param \Psr\Http\Message\StreamInterface $stream The stream to get a resource for
     * @return resource
     * @throws \InvalidArgumentException if stream is not readable or writable
     */
    public static function getResource(StreamInterface $stream)
    {
        self::register();

        if ($stream->isReadable()) {
            $mode = $stream->isWritable() ? 'r+' : 'r';
        } elseif ($stream->isWritable()) {
            $mode = 'w';
        } else {
            throw new InvalidArgumentException('The stream must be readable, '
                . 'writable, or both.');
        }

        return fopen('php://memory', $mode, false, self::createStreamContext($stream));
    }

    /**
     * Creates a stream context that can be used to open a stream as a php stream resource.
     *
     * @param \Psr\Http\Message\StreamInterface $stream PSR Stream Interface
     * @return resource
     */
    public static function createStreamContext(StreamInterface $stream)
    {
        return stream_context_create([
            'php' => ['stream' => $stream]
        ]);
    }

    /**
     * Registers the stream wrapper if needed
     */
    public static function register(): void
    {
        if (!in_array('php', stream_get_wrappers())) {
            stream_wrapper_register('php', __CLASS__);
        }
    }

    public function stream_open(string $path, string $mode, int $options, string &$opened_path = null): bool
    {
        $options = stream_context_get_options($this->context);

        if (!isset($options['php']['stream'])) {
            return false;
        }

        $this->mode = $mode;
        $this->stream = $options['php']['stream'];

        return true;
    }

    /**
     * @param int $count Count
     * @return string
     */
    public function stream_read(int $count): string
    {
        return $this->stream->read($count);
    }

    /**
     * @param string $data Data
     * @return int
     */
    public function stream_write(string $data): int
    {
        return $this->stream->write($data);
    }

    /**
     * @return int
     */
    public function stream_tell(): int
    {
        return $this->stream->tell();
    }

    /**
     * @return bool
     */
    public function stream_eof(): bool
    {
        return $this->stream->eof();
    }

    /**
     * @param int $offset Offset
     * @param int $whence Whence
     * @return bool
     */
    public function stream_seek(int $offset, int $whence): bool
    {
        $this->stream->seek($offset, $whence);

        return true;
    }

    /**
     * @return resource|false
     */
    public function stream_cast(int $cast_as)
    {
        $stream = clone($this->stream);
        $resource = $stream->detach();

        return $resource ?? false;
    }

    /**
     * @return array<int|string, int>
     */
    public function stream_stat(): array
    {
        static $modeMap = [
            'r'  => 33060,
            'rb' => 33060,
            'r+' => 33206,
            'w'  => 33188,
            'wb' => 33188
        ];

        return [
            'dev'     => 0,
            'ino'     => 0,
            'mode'    => $modeMap[$this->mode],
            'nlink'   => 0,
            'uid'     => 0,
            'gid'     => 0,
            'rdev'    => 0,
            'size'    => $this->stream->getSize() ?: 0,
            'atime'   => 0,
            'mtime'   => 0,
            'ctime'   => 0,
            'blksize' => 0,
            'blocks'  => 0
        ];
    }

    /**
     * @param string $path Path
     * @param int $flags Flags
     * @return array<int|string, int>
     */
    public function url_stat(string $path, int $flags): array
    {
        return [
            'dev'     => 0,
            'ino'     => 0,
            'mode'    => 0,
            'nlink'   => 0,
            'uid'     => 0,
            'gid'     => 0,
            'rdev'    => 0,
            'size'    => 0,
            'atime'   => 0,
            'mtime'   => 0,
            'ctime'   => 0,
            'blksize' => 0,
            'blocks'  => 0
        ];
    }
}
