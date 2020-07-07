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
use Phauthentic\Infrastructure\Storage\UrlBuilder\UrlBuilderInterface;

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
     * @return resource|null
     */
    public function resource();

    /**
     * Collection name
     *
     * @return null|string
     */
    public function collection(): ?string;

    /**
     * Adds the file to a collection
     *
     * @param string $collection Collection
     * @return $this
     */
    public function addToCollection(string $collection): self;

    /**
     * Get a variant
     *
     * @param string $name
     * @return array
     */
    public function variant(string $name): array;

    /**
     * Array data structure of the variants
     *
     * @return array
     */
    public function variants(): array;

    /**
     * Checks if the file has a specific variant
     *
     * @param string $name
     * @return bool
     */
    public function hasVariant(string $name): bool;

    /**
     * Checks if the file has any variants
     *
     * @return bool
     */
    public function hasVariants(): bool;

    /**
     * @param string $name Name
     * @param array $data Data
     * @return $this
     */
    public function withVariant(string $name, array $data): self;

    /**
     * @param array $variants Variants
     * @param bool $merge Merge variants, default is false
     * @return $this
     */
    public function withVariants(array $variants, bool $merge = true): self;

    /**
     * Gets the paths for all variants
     *
     * @return array
     */
    public function variantPaths(): array;

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
     * Removes all metadata
     *
     * @return static
     */
    public function withoutMetadata(): self;

    /**
     * Adds a single key and value to the metadata array
     *
     * @param string $key Key
     * @param mixed $data
     * @return self
     */
    public function withMetadataKey(string $key, $data): self;

    /**
     * Removes a key from the metadata array
     * @param string $name Name
     * @return $this
     */
    public function withoutMetadataKey(string $name): self;

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
     * Builds the URL for this file
     *
     * @param \Phauthentic\Infrastructure\Storage\UrlBuilder\UrlBuilderInterface $urlBuilder URL Builder
     * @return $this
     */
    public function buildUrl(UrlBuilderInterface $urlBuilder): self;

    /**
     * Gets the URL for the file
     *
     * @return string
     */
    public function url(): string;

    /**
     * Sets a URL
     *
     * @param string $url URL
     * @return self
     */
    public function withUrl(string $url): self;

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

    /**
     * UUID of the file
     *
     * @param string $uuid UUID string
     * @return self
     */
    public function withUuid(string $uuid): self;

    /**
     * Filename
     *
     * @param string $filename Filename
     * @return self
     */
    public function withFilename(string $filename): self;

    /**
     * Assign a model and model id to a file
     *
     * @param string $model Model
     * @param string|int $modelId Model ID, UUID string or integer
     * @return $this
     */
    public function belongsToModel(string $model, $modelId): self;
}
