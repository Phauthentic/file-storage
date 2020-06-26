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

namespace Phauthentic\Infrastructure\Storage\Processor\Image;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Phauthentic\Infrastructure\Storage\Processor\Exception\VariantExistsException;

/**
 * Conversion Collection
 */
class ImageVariantCollection implements JsonSerializable, IteratorAggregate, Countable
{
    /**
     * @var array
     */
    protected array $variants = [];

    /**
     * @return self
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * @param array $variants Variant array structure
     * @return self
     */
    public static function fromArray(array $variants)
    {
        $that = new self();

        foreach ($variants as $name => $data) {
            $variant = ImageVariant::create($name);
            if (isset($data['optimize']) && $data['optimize'] === true) {
                $variant = $variant->optimize();
            }

            if (!empty($data['path']) && is_string($data['path'])) {
                $variant = $variant->withPath($data['path']);
            }

            foreach ($data['operations'] as $method => $args) {
                if (!method_exists($variant, $method)) {
                    throw new \RuntimeException('Operation not supported');
                }

                $variant = call_user_func_array([$variant, $method], $args);
            }

            $that->add($variant);
        }

        return $that;
    }

    /**
     * @param string $name Name
     * @return \Phauthentic\Infrastructure\Storage\Processor\Image\ImageVariant
     */
    public function addNew(string $name)
    {
        $this->add(ImageVariant::create($name));

        return $this->get($name);
    }

    /**
     * Gets a manipulation from the collection
     *
     * @return \Phauthentic\Infrastructure\Storage\Processor\Image\ImageVariant
     */
    public function get($name): ImageVariant
    {
        return $this->variants[$name];
    }

    /**
     * @param \Phauthentic\Infrastructure\Storage\Processor\Image\ImageVariant $variant Variant
     * @return void
     */
    public function add(ImageVariant $variant): void
    {
        if ($this->has($variant->name())) {
            throw VariantExistsException::withName($variant->name());
        }

        $this->variants[$variant->name()] = $variant;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->variants[$name]);
    }

    /**
     * @param string $name
     */
    public function remove(string $name): void
    {
        unset($this->variants[$name]);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        return new ArrayIterator($this->variants);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $array = [];
        foreach ($this->variants as $variant) {
            $array[$variant->name()] = $variant->toArray();
        }

        return $array;
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return count($this->variants);
    }
}
