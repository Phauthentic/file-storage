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
use Phauthentic\Infrastructure\Storage\PathBuilder\PathBuilder;
use Phauthentic\Infrastructure\Storage\Processor\Image\ImageVariantCollection;
use Phauthentic\Test\TestCase\TestCase;

/**
 * PathBuilderTest
 */
class PathBuilderTest extends TestCase
{
    /**
     * @return void
     */
    public function testDatePaths(): void
    {
        /** @var \Phauthentic\Infrastructure\Storage\PathBuilder\PathBuilder|\PHPUnit\Framework\MockObject\MockObject $builder */
        $builder = $this->getMockBuilder(PathBuilder::class)
            ->setConstructorArgs([
                [
                    'pathTemplate' => '{year}{ds}{month}{ds}{day}{ds}{hour}{ds}{minute}'
                ]
            ])
            ->setMethods(['getDateObject'])
            ->getMock();

        $builder->expects($this->any())
            ->method('getDateObject')
            ->willReturn((new \DateTime('2020-01-01T20:00:00')));

        $file = $this->getFixtureFile('titus.jpg');
        $file = FileFactory::fromDisk($file, 'local')
            ->withUuid('914e1512-9153-4253-a81e-7ee2edc1d973');

        $result = $builder->path($file);

        $this->assertEquals($this->sanitizeSeparator('2020/01/01/20/00'), $result);
    }

    /**
     * @return void
     */
    public function testPathWithEmptyPlaceHolders(): void
    {
        $file = $this->getFixtureFile('titus.jpg');
        $file = FileFactory::fromDisk($file, 'local')
            ->withUuid('914e1512-9153-4253-a81e-7ee2edc1d973');

        $builder = new PathBuilder();
        $result = $builder->path($file);

        $this->assertEquals($this->sanitizeSeparator('/fe/c3/b4/914e151291534253a81e7ee2edc1d973/titus.jpg'), $result);
    }

    /**
     * @return void
     */
    public function testBuilder(): void
    {
        $collection = ImageVariantCollection::create();
        $collection
            ->addNew('resizeAndFlip')
            ->flipHorizontal()
            ->resize(300, 300)
            ->optimize();

        $file = $this->getFixtureFile('titus.jpg');
        $file = FileFactory::fromDisk($file, 'local')
            ->withUuid('914e1512-9153-4253-a81e-7ee2edc1d973')
            ->addToCollection('avatar')
            ->belongsToModel('User', '1')
            ->withVariants($collection->toArray());

        $builder = new PathBuilder();

        $result = $builder->path($file);
        $this->assertEquals(
            $this->sanitizeSeparator('User\fe\c3\b4\914e151291534253a81e7ee2edc1d973\titus.jpg'),
            $result
        );

        $result = $builder->pathForVariant($file, 'resizeAndFlip');
        $this->assertEquals(
            $this->sanitizeSeparator('User\fe\c3\b4\914e151291534253a81e7ee2edc1d973\titus.7ae239.jpg'),
            $result
        );
    }
}
