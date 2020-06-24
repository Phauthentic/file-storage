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

use Phauthentic\Infrastructure\Storage\Exception\InvalidStreamResourceException;
use Phauthentic\Infrastructure\Storage\PathBuilder\PathBuilderInterface;
use RuntimeException;

use function pathinfo;

/**
 * File
 */
class File implements FileInterface
{
    /**
     * @var int
     */
    protected int $id;

    /**
     * @var string
     */
    protected string $uuid;

    /**
     * @var string
     */
    protected string $filename;

    /**
     * @var int
     */
    protected int $filesize;

    /**
     * @var string
     */
    protected ?string $mimeType = null;

    /**
     * @var string|null
     */
    protected ?string $extension = null;

    /**
     * @var string
     */
    protected ?string $path = null;

    /**
     * @var string|null
     */
    protected ?string $collection = null;

    /**
     * @var string
     */
    protected string $storage = 'local';

    /**
     * @var array
     */
    protected array $metadata = [];

    /**
     * @var string|null
     */
    protected ?string $model = null;

    /**
     * @var string|null
     */
    protected ?string $modelId = null;

    /**
     * Source file to be stored in our system
     *
     * @var mixed
     */
    protected $sourceFile;

    /**
     * @var resource
     */
    protected $resource;

    /**
     * @var array
     */
    protected array $manipulations = [];

    /**
     * Creates a new instance
     *
     * @param string $filename Filename
     * @param int $filesize Filesize
     * @param string|null $mimeType Mime Type
     * @param string $storage Storage config name
     * @param string|null $collection Collection name
     * @param string|null $model Model name
     * @param string|null $modelId Model id
     * @param array $manipulations Manipulations
     * @param array $metadata Meta data
     * @param resource|null $resource
     * @return self
     */
    public static function create(
        string $filename,
        int $filesize,
        ?string $mimeType,
        string $storage,
        ?string $collection = null,
        ?string $model = null,
        ?string $modelId = null,
        array $manipulations = [],
        array $metadata = [],
        $resource = null
    ): self {
        $that = new self();

        $that->filename = $filename;
        $that->filesize = $filesize;
        $that->mimeType = $mimeType;
        $that->storage = $storage;
        $that->model = $model;
        $that->modelId = $modelId;
        $that->collection = $collection;
        $that->manipulations = $manipulations;
        $that->metadata = $metadata;

        if ($resource !== null) {
            $that = $that->withResource($resource);
        }

        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $that->extension = empty($extension) ? null : (string)$extension;

        return $that;
    }

    /**
     * Storage name
     *
     * @return string
     */
    public function storage(): string
    {
        return $this->storage;
    }

    /**
     * UUID of the file
     *
     * @param string $uuid UUID string
     * @return self
     */
    public function withUuid(string $uuid): self
    {
        $that = clone $this;
        $that->uuid = $uuid;

        return $that;
    }

    /**
     * Stream resource that should be stored
     *
     * @return resource|null
     */
    public function resource()
    {
        return $this->resource;
    }

    /**
     * Same as withResource() but takes a file path
     *
     * @param string $file File
     * @return self
     */
    public function withFile(string $file): self
    {
        return $this->withResource(fopen($file, 'rb'));
    }

    /**
     * @param mixed $resource
     */
    protected function assertStreamResource($resource): void
    {
        if (
            !is_resource($resource)
            || get_resource_type($resource) !== 'stream'
        ) {
            throw InvalidStreamResourceException::create();
        }
    }

    /**
     * Stream resource of the file to be stored
     *
     * @param resource  $resource
     * @return self
     */
    public function withResource($resource): self
    {
        $this->assertStreamResource($resource);

        $that = clone $this;
        $that->resource = $resource;

        return $that;
    }

    /**
     * Assign a model and model id to a file
     *
     * @param string $model Model
     * @param string|int $modelId Model ID, UUID string or integer
     * @return $this
     */
    public function belongsToModel(string $model, $modelId): self
    {
        $this->model = $model;
        $this->modelId = $modelId;

        return $this;
    }

    /**
     * Adds the file to a collection
     *
     * @param string $collection Collection
     * @return $this
     */
    public function addToCollection(string $collection): self
    {
        $this->collection = $collection;

        return $this;
    }

    /**
     * Sets the path, immutable
     *
     * @param string $path Path to the file
     * @return $this
     */
    public function withPath(string $path): self
    {
        $that = clone $this;
        $that->path = $path;

        return $that;
    }

    /**
     * Filename
     *
     * @param string $filename Filename
     * @return self
     */
    public function withFilename(string $filename): self
    {
        $that = clone $this;
        $that->filename = $filename;

        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $that->extension = empty($extension) ? null : (string)$extension;

        return $that;
    }

    /**
     * The collections name this file belongs into
     *
     * @return string|null
     */
    public function collection(): ?string
    {
        return $this->collection;
    }

    /**
     * Model name
     *
     * @return string|null
     */
    public function model(): ?string
    {
        return $this->model;
    }

    /**
     * Model ID
     *
     * @return string|null
     */
    public function modelId(): ?string
    {
        return $this->modelId;
    }

    /**
     * Size of the file in bytes
     *
     * @return int
     */
    public function filesize(): int
    {
        return $this->filesize;
    }

    /**
     * Returns a human readable file size
     *
     * @return string
     */
    public function readableSize(): string
    {
        $i = floor(log($this->filesize, 1024));
        $round = (string)round($this->filesize / (1024 ** $i), [0, 0, 2, 2, 3][$i]);

        return $round . ['B','kB','MB','GB','TB'][$i];
    }

    /**
     * @return string|null
     */
    public function extension(): ?string
    {
        return $this->extension;
    }

    /**
     * @return string
     */
    public function mimeType(): string
    {
        return $this->mimeType;
    }

    /**
     * @return string
     */
    public function filename(): string
    {
        return $this->filename;
    }

    /**
     * @return string
     */
    public function uuid(): string
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public function path(): string
    {
        if ($this->path === null) {
            throw new RuntimeException(
                'Path has not been set'
            );
        }

        return $this->path;
    }

    /**
     * Builds the path for this file
     *
     * @param \Phauthentic\Infrastructure\Storage\PathBuilder\PathBuilderInterface $pathBuilder Path Builder
     * @return $this
     */
    public function buildPath(PathBuilderInterface $pathBuilder): self
    {
        if ($this->path !== null) {
            throw new RuntimeException(
                'This file has already a path'
            );
        }

        $that = clone $this;
        $that->path = $pathBuilder->path($this);

        return $that;
    }

    /**
     * @param array $metadata Meta data
     * @return $this
     */
    public function withMetadata(array $metadata): self
    {
        $that = clone $this;
        $that->metadata = $metadata;

        return $that;
    }

    /**
     * @param string $name Name
     * @param mixed $data Data
     * @return $this
     */
    public function withMetadataKey(string $name, $data): self
    {
        $that = clone $this;
        $that->metadata[$name] = $data;

        return $that;
    }

    /**
     * @param string $name Name
     * @return $this
     */
    public function withoutMetadataKey(string $name): self
    {
        $that = clone $this;
        unset($that->metadata[$name]);

        return $that;
    }

    /**
     * @return array
     */
    public function metadata(): array
    {
        return $this->metadata;
    }

    /**
     * @return bool
     */
    public function hasManipulations(): bool
    {
        return !empty($this->manipulations);
    }

    /**
     * @param string $name Name
     * @return bool
     */
    public function hasManipulation(string $name): bool
    {
        return isset($this->manipulations[$name]);
    }

    /**
     * @return array
     */
    public function manipulations(): array
    {
        return $this->manipulations;
    }

    /**
     * Returns a manipulation setting by name
     *
     * @param string $name Name
     * @return array
     */
    public function manipulation(string $name): array
    {
        if (!isset($this->manipulations[$name])) {
            throw new RuntimeException(
                'Manipulation does not exist'
            );
        }

        return $this->manipulations[$name];
    }

    /**
     * Adds a manipulation
     *
     * @param string $name Name
     * @param array $data Data
     * @return $this
     */
    public function withManipulation(string $name, array $data): self
    {
        $that = clone $this;
        $that->manipulations[$name] = $data;

        return $that;
    }

    /**
     * Gets the paths for all manipulations
     *
     * @return array
     */
    public function manipulationPaths(): array
    {
        $paths = [];
        foreach ($this->manipulations as $manipulation => $data) {
            if (isset($data['path'])) {
                $paths[$manipulation] = $data['path'];
            }
        }

        return $paths;
    }

    /**
     * Sets many manipulations at once
     *
     * @param array $manipulations Manipulations
     * @param bool $merge Merge manipulations, default is true
     * @return $this
     */
    public function withManipulations(array $manipulations, bool $merge = true): self
    {
        $that = clone $this;
        $that->manipulations = array_merge_recursive(
            $merge ? $that->manipulations : [],
            $manipulations
        );

        return $that;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'filename' => $this->filename,
            'filesize' => $this->filesize,
            'mimeType' => $this->mimeType,
            'extension' => $this->extension,
            'path' => $this->path,
            'model' => $this->model,
            'modelId' => $this->modelId,
            'collection' => $this->collection,
            'readableSize' => $this->readableSize(),
            'manipulations' => $this->manipulations,
            'metaData' => $this->metadata,
        ];
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
