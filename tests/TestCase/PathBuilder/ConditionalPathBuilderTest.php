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

namespace Phauthentic\Test\TestCase\PathBuilder;

use Phauthentic\Infrastructure\Storage\FileFactory;
use Phauthentic\Infrastructure\Storage\FileInterface;
use Phauthentic\Infrastructure\Storage\PathBuilder\ConditionalPathBuilder;
use Phauthentic\Infrastructure\Storage\PathBuilder\PathBuilder;
use Phauthentic\Infrastructure\Storage\PathBuilder\PathBuilderInterface;
use Phauthentic\Test\TestCase\TestCase;

/**
 * ConditionalPathBuilderTest
 */
class ConditionalPathBuilderTest extends TestCase
{
    /**
     * @return void
     */
    public function testBuilder(): void
    {
        $fixtureFile = $this->getFixtureFile('titus.jpg');
        $file1 = FileFactory::fromDisk($fixtureFile, 'local')
            ->belongsToModel('User', '1');

        $file2 = FileFactory::fromDisk($fixtureFile, 'local')
            ->belongsToModel('Photos', '1');

        /** @var \Phauthentic\Infrastructure\Storage\PathBuilder\PathBuilderInterface|\PHPUnit\Framework\MockObject\MockObject $defaultBuilder */
        $defaultBuilder = $this->getMockBuilder(PathBuilderInterface::class)
            ->getMock();
        $defaultBuilder->expects($this->once())
            ->method('path')
            ->willReturn('');

        /** @var \Phauthentic\Infrastructure\Storage\PathBuilder\PathBuilderInterface|\PHPUnit\Framework\MockObject\MockObject $otherPathBuilder */
        $otherPathBuilder = $this->getMockBuilder(PathBuilderInterface::class)
            ->getMock();
        $otherPathBuilder->expects($this->once())
            ->method('path')
            ->willReturn('');

        $conditionalBuilder = new ConditionalPathBuilder($defaultBuilder);

        $conditionalBuilder->addPathBuilder($otherPathBuilder, function (FileInterface $file) {
            return $file->model() === 'User';
        });

        $conditionalBuilder->path($file1);
        $conditionalBuilder->path($file2);
    }

    /**
     * @return void
     */
    public function testPathForVariant(): void
    {
        $fixtureFile = $this->getFixtureFile('titus.jpg');
        $file = FileFactory::fromDisk($fixtureFile, 'local')
            ->withUuid('914e1512-9153-4253-a81e-7ee2edc1d973')
            ->belongsToModel('User', '1');

        $file->withVariant('resize', [
            'operations' => [
                'resize' => [100, 100]
            ]
        ]);

        $defaultBuilder = new PathBuilder();
        $pathBuilder = new PathBuilder();
        $conditionalBuilder = new ConditionalPathBuilder($defaultBuilder);
        $conditionalBuilder->addPathBuilder($pathBuilder, function (FileInterface $file) {
            return $file->model() !== 'User';
        });

        $result = $conditionalBuilder->pathForVariant($file, 'resize');
        $this->assertSame($this->sanitizeSeparator('User\fe\c3\b4\914e151291534253a81e7ee2edc1d973\titus.73db01.jpg'), $result);
    }
}
