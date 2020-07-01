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

use InvalidArgumentException;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use Phauthentic\Infrastructure\Storage\PathBuilder\PathBuilder;
use Phauthentic\Infrastructure\Storage\PathBuilder\PathBuilderInterface;
use RuntimeException;

/**
 * File Storage
 */
class FileStorage implements FileStorageInterface
{
    /**
     * @var array
     */
    protected array $callbacks = [
        'beforeSave' => [],
        'afterSave' => [],
        'beforeRemove' => [],
        'afterRemove' => [],
    ];

    /**
     * @var \Phauthentic\Infrastructure\Storage\PathBuilder\PathBuilderInterface
     */
    protected PathBuilderInterface $pathBuilder;

    /**
     * @var \Phauthentic\Infrastructure\Storage\StorageServiceInterface
     */
    protected StorageServiceInterface $storageService;

    /**
     * Constructor
     *
     * @param \Phauthentic\Infrastructure\Storage\StorageServiceInterface $storageService Storage Service
     * @param \Phauthentic\Infrastructure\Storage\PathBuilder\PathBuilderInterface $pathBuilder Path Builder
     */
    public function __construct(
        StorageServiceInterface $storageService,
        ?PathBuilderInterface $pathBuilder = null
    ) {
        $this->pathBuilder = $pathBuilder ?? new PathBuilder();
        $this->storageService = $storageService;
    }

    /**
     * @param string $type Type
     * @param \Phauthentic\Infrastructure\Storage\FileInterface $file File
     * @return \Phauthentic\Infrastructure\Storage\FileInterface
     */
    public function runCallbacks(string $type, FileInterface $file): FileInterface
    {
        if (!isset($this->callbacks[$type])) {
            throw new InvalidArgumentException(sprintf(
                'Type %s is invalid',
                $type
            ));
        }

        foreach ($this->callbacks[$type] as $callback) {
            $file = $callback($file);
        }

        return $file;
    }

    /**
     * @inheritDoc
     */
    public function store(FileInterface $file): FileInterface
    {
        $config = new Config();

        $file = $file->buildPath($this->pathBuilder);
        $file = $this->runCallbacks('beforeSave', $file);

        $storage = $this->getStorage($file->storage());
        $storage->write($file->path(), $file->resource(), $config);

        return $this->runCallbacks('afterSave', $file);
    }

    /**
     * @inheritDoc
     */
    public function remove(FileInterface $file): FileInterface
    {
        $file = $this->runCallbacks('beforeRemove', $file);

        $this->getStorage($file->storage())->delete($file->path());
        foreach ($file->variants() as $variant) {
            if (!empty($variant['path'])) {
                $this->getStorage($file->storage())->delete($variant['path']);
            }
        }

        return $this->runCallbacks('afterRemove', $file);
    }

    /**
     * @inheritDoc
     */
    public function removeVariant(FileInterface $file, string $name): FileInterface
    {
        if (!$file->hasVariants()) {
            throw new RuntimeException(sprintf(
                'Manipulation `%s` does not exist',
                $name
            ));
        }

        $variant = $file->variant($name);
        if (!empty($variant['path'])) {
            $this->getStorage($file->storage())->delete($variant['path']);
        }

        $variants = $file->variants();
        unset($variants[$name]);

        return $file->withVariants($variants, false);
    }

    /**
     * Gets the storage abstraction to use
     *
     * @param string $storage Storage name to use
     * @return \League\Flysystem\AdapterInterface
     */
    public function getStorage(string $storage): AdapterInterface
    {
        return $this->storageService->adapter($storage);
    }
}
