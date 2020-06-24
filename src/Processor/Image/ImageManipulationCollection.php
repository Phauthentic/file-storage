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
use IteratorAggregate;
use JsonSerializable;
use RuntimeException;

/**
 * Conversion Collection
 */
class ImageManipulationCollection implements JsonSerializable, IteratorAggregate
{
    /**
     * @var array
     */
    protected array $manipulations;

    /**
     * @return self
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * @param array $manipulations Manipulations array structure
     * @return self
     */
    public static function fromArray(array $manipulations)
    {
        $that = new self();

        foreach ($manipulations as $name => $data) {
            $manipulation = ImageManipulation::create($name);
            if (isset($data['optimize']) && $data['optimize'] === true) {
                $manipulation = $manipulation->optimize();
            }

            if (!empty($data['path']) && is_string($data['path'])) {
                $manipulation = $manipulation->withPath($data['path']);
            }

            foreach ($data['operations'] as $method => $args) {
                if (method_exists($manipulation, $method)) {
                    call_user_func_array([$manipulation, $method], $args);
                }
            }

            $that->add($manipulation);
        }

        return $that;
    }

    public function addNew($name)
    {
        $this->add(ImageManipulation::create($name));

        return $this->get($name);
    }

    /**
     * Gets a manipulation from the collection
     *
     * @return \Phauthentic\Infrastructure\Storage\Processor\Image\ImageManipulation
     */
    public function get($name): ImageManipulation
    {
        return $this->manipulations[$name];
    }

    /**
     * @param \Phauthentic\Infrastructure\Storage\Processor\Image\ImageManipulation $manipulation Manipulation
     * @return void
     */
    public function add(ImageManipulation $manipulation): void
    {
        if ($this->has($manipulation->name())) {
            throw new RuntimeException(sprintf(
                'A manipulation with the name `%s` already exists',
                $manipulation->name()
            ));
        }

        $this->manipulations[$manipulation->name()] = $manipulation;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->manipulations[$name]);
    }

    /**
     * @param string $name
     */
    public function remove(string $name): void
    {
        unset($this->manipulations[$name]);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return $this->manipulations;
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        return new ArrayIterator($this->manipulations);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $array = [];
        foreach ($this->manipulations as $manipulation) {
            $array[$manipulation->name()] = $manipulation->toArray();
        }

        return $array;
    }
}
