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

use Phauthentic\Infrastructure\Storage\FileInterface;

/**
 * Add callbacks and path builders to check on the file which of the builders
 * should be used to build the path.
 *
 * This allows you to use different instances with a different configuration or
 * implementation of path builders for files of different types or in different
 * collections or models.
 */
class ConditionalPathBuilder implements PathBuilderInterface
{
    /**
     * @var array
     */
    protected array $pathBuilders = [];

    /**
     * @var \Phauthentic\Infrastructure\Storage\PathBuilder\PathBuilderInterface
     */
    protected PathBuilderInterface $defaultPathBuilder;

    /**
     * @param \Phauthentic\Infrastructure\Storage\PathBuilder\PathBuilderInterface $pathBuilder Default Path Builder
     */
    public function __construct(PathBuilderInterface $pathBuilder)
    {
        $this->defaultPathBuilder = $pathBuilder;
    }

    /**
     * @param callable $conditionCheck Condition check
     * @param \Phauthentic\Infrastructure\Storage\PathBuilder\PathBuilderInterface $pathBuilder Path Builder
     * @return $this
     */
    public function addPathBuilder(callable $conditionCheck, PathBuilderInterface $pathBuilder): self
    {
        $this->pathBuilders[] = [
            'callable' => $conditionCheck,
            'pathBuilder' => $pathBuilder
        ];

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function path(FileInterface $file, array $options = []): string
    {
        foreach ($this->pathBuilders as $builder) {
            if ($builder['callable']($file)) {
                return $builder['pathBuilder']->path($file, $options);
            }
        }

        return $this->defaultPathBuilder->path($file, $options);
    }

    /**
     * @inheritDoc
     */
    public function pathForManipulation(FileInterface $file, string $name, array $options = []): string
    {
        foreach ($this->pathBuilders as $builder) {
            if ($builder['callable']($file)) {
                return $builder['pathBuilder']->pathForManipulation($file, $name, $options);
            }
        }

        return $this->defaultPathBuilder->pathForManipulation($file, $name, $options);
    }
}
