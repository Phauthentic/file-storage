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

use Phauthentic\Infrastructure\Storage\File;
use InvalidArgumentException;
use Phauthentic\Infrastructure\Storage\FileFactory;
use Phauthentic\Infrastructure\Storage\PathBuilder\PathBuilder;
use Phauthentic\Infrastructure\Storage\Processor\Image\ImageVariantCollection;
use Phauthentic\Infrastructure\Storage\Utility\FilenameSanitizer;
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

    /**
     * @return void
     */
    public function testSetters(): void
    {
        $builder = new PathBuilder();

        $sanitizer = new FilenameSanitizer();
        $result = $builder->setFilenameSanitizer($sanitizer);
        $this->assertSame($builder, $result);

        $result = $builder->setPathTemplate('{model}/{filename}');
        $this->assertSame($builder, $result);

        $result = $builder->setVariantPathTemplate('{model}/{filename}.{variant}');
        $this->assertSame($builder, $result);

        $result = $builder->setCustomDateFormat('Y-m-d H:i:s');
        $this->assertSame($builder, $result);
    }

    /**
     * @return void
     */
    public function testCustomTemplates(): void
    {
        $file = $this->getFixtureFile('titus.jpg');
        $file = FileFactory::fromDisk($file, 'local')
            ->withUuid('914e1512-9153-4253-a81e-7ee2edc1d973')
            ->belongsToModel('User', '1');

        $builder = new PathBuilder();
        $builder->setPathTemplate('{model}/{filename}');

        $result = $builder->path($file);
        $this->assertEquals('User/titus', $result);

        $builder->setVariantPathTemplate('{model}/{filename}.{variant}.{extension}');
        $result = $builder->pathForVariant($file, 'thumb');
        $this->assertEquals('User/titus.thumb.jpg', $result);
    }

    /**
     * @return void
     */
    public function testFilenameWithoutExtension(): void
    {
        $file = FileFactory::fromDisk($this->getFixtureFile('titus.jpg'), 'local')
            ->withUuid('914e1512-9153-4253-a81e-7ee2edc1d973')
            ->belongsToModel('User', '1')
            ->withFilename('testfile'); // No extension

        $builder = new PathBuilder();
        $builder->setPathTemplate('{filename}.{extension}');

        $result = $builder->path($file);
        $this->assertEquals('testfile', $result);
    }

    /**
     * @return void
     */
    public function testRandomPathWithCallable(): void
    {
        $file = File::create('test.jpg', 1000, 'image/jpeg', 'local')
            ->withUuid('12345678-1234-1234-1234-123456789012');

        // Create a subclass to test callable randomPath
        $builder = new class extends PathBuilder {
            private $callable;

            public function setRandomPathCallable(callable $callable): void
            {
                $this->callable = $callable;
            }

            protected function randomPath($string, $level = 3, $method = 'sha1'): string
            {
                if ($this->callable) {
                    return ($this->callable)($string, $level);
                }
                return parent::randomPath($string, $level, $method);
            }
        };

        $builder->setRandomPathCallable(function ($string, $level) {
            $path = '';
            for ($i = 0; $i < $level; $i++) {
                $path .= 'custom' . DIRECTORY_SEPARATOR;
            }
            return rtrim($path, DIRECTORY_SEPARATOR);
        });

        $builder->setPathTemplate('{randomPath}{filename}.{extension}');

        $result = $builder->path($file);
        $this->assertEquals('custom/custom/customtest.jpg', $result);
    }


    /**
     * @return void
     */
    public function testTemplateParsingEmptyPlaceholders(): void
    {
        $file = File::create('test.jpg', 1000, 'image/jpeg', 'local')
            ->withUuid('12345678-1234-1234-1234-123456789012');

        $builder = new PathBuilder();
        $builder->setPathTemplate('{model}/{collection}/{randomPath}{filename}.{extension}');

        $result = $builder->path($file);
        // Empty placeholders should result in extra separators that get cleaned up
        $this->assertStringNotContainsString('//', $result);
        $this->assertEquals('/80/e8/3atest.jpg', $result);
    }

    /**
     * @return void
     */
    public function testTemplateParsingDoubleSeparators(): void
    {
        $file = File::create('test.jpg', 1000, 'image/jpeg', 'local')
            ->withUuid('12345678-1234-1234-1234-123456789012');

        $builder = new PathBuilder();
        $builder->setPathTemplate('{ds}{ds}{randomPath}{ds}{ds}{filename}.{extension}');

        $result = $builder->path($file);
        // Double separators should be cleaned up
        $this->assertStringNotContainsString('//', $result);
        $this->assertEquals('/80/e8/3a/test.jpg', $result);
    }

    /**
     * @return void
     */
    public function testTemplateParsingEndingWithDot(): void
    {
        $file = File::create('test.jpg', 1000, 'image/jpeg', 'local')
            ->withUuid('12345678-1234-1234-1234-123456789012');

        $builder = new PathBuilder();
        $builder->setPathTemplate('{filename}.');

        $result = $builder->path($file);
        // Path ending with dot should have the dot removed if no extension
        $this->assertEquals('test', $result);
    }

    /**
     * @return void
     */
    public function testPathForVariant(): void
    {
        $file = File::create('test.jpg', 1000, 'image/jpeg', 'local')
            ->withUuid('12345678-1234-1234-1234-123456789012')
            ->withVariant('thumb', ['width' => 100]);

        $builder = new PathBuilder();
        $builder->setVariantPathTemplate('{randomPath}/{filename}.thumb.{extension}');

        $result = $builder->pathForVariant($file, 'thumb');
        $this->assertEquals('80/e8/3a/test.thumb.jpg', $result);
    }
}
