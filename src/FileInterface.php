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

namespace Phauthentic\Infrastructure\Storage;

use JsonSerializable;
use Phauthentic\Infrastructure\Storage\PathBuilder\PathBuilderInterface;

/**
 * File Interface
 */
interface FileInterface extends JsonSerializable
{
    /**
     * The uuid of the file
     *
     * @return string
     */
    public function uuid(): string;

    /**
     * Filename
     *
     * @return string
     */
    public function filename(): string;

    /**
     * Filesize
     *
     * @return int
     */
    public function filesize(): int;

    /**
     * Mime Type
     *
     * @return null|string
     */
    public function mimeType(): ?string;

    /**
     * Model name
     *
     * @return null|string
     */
    public function model(): ?string;

    /**
     * Model ID
     *
     * @return string
     */
    public function modelId(): ?string;

    /**
     * Gets the metadata array
     *
     * @return array
     */
    public function metadata(): array;

    /**
     * Resource to store
     *
     * @return resource
     * @return static
     */
    public function resource();

    /**
     * Collection name
     *
     * @return null|string
     */
    public function collection(): ?string;

    /**
     * Manipulation
     *
     * @param string $name
     * @return array
     */
    public function manipulation(string $name): array;

    /**
     * Array data structure of the manipulations
     *
     * @return array
     */
    public function manipulations(): ?array;

    /**
     * Checks if the file has any manipulations configure
     *
     * @return bool
     */
    public function hasManipulations(): bool;

    /**
     * @param string $name Name
     * @param array $data Data
     * @return $this
     */
    public function withManipulation(string $name, array $data): self;

    /**
     * @param array $manipulations Manipulations
     * @param bool $merge Merge manipulations, default is false
     * @return $this
     */
    public function withManipulations(array $manipulations, bool $merge = true): self;

    /**
     * Returns an array of the file data
     *
     * @return array
     */
    public function toArray(): array;

    /**
     * Adds (replaces) the existing metadata
     *
     * @param array $metadata Metadata
     * @return static
     */
    public function withMetadata(array $metadata): self;

    /**
     * Adds a single key and value to the metadata array
     *
     * @param string $key Key
     * @param mixed $data
     * @return self
     */
    public function withMetadataKey(string $key, $data): self;

    /**
     * Stream resource of the file to be stored
     *
     * @param resource  $resource
     * @return self
     */
    public function withResource($resource): self;

    /**
     * Same as withResource() but takes a file path
     *
     * @param string $file File
     * @return self
     */
    public function withFile(string $file): self;

    /**
     * Returns the path for the file in the storage system
     *
     * @return string
     */
    public function path(): string;

    /**
     * Sets the path, immutable
     *
     * @param string $path Path to the file
     * @return $this
     */
    public function withPath(string $path): self;

    /**
     * Builds the path for this file
     *
     * @param \Phauthentic\Infrastructure\Storage\PathBuilder\PathBuilderInterface $pathBuilder Path Builder
     * @return $this
     */
    public function buildPath(PathBuilderInterface $pathBuilder): self;

    /**
     * Storage name
     *
     * @return string
     */
    public function storage(): string;

    /**
     * Returns the filenames extension
     *
     * @return string|null
     */
    public function extension(): ?string;
}
