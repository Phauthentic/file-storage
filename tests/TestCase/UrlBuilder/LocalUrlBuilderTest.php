<?php

declare(strict_types=1);

namespace Phauthentic\Test\TestCase\UrlBuilder;

use Phauthentic\Infrastructure\Storage\FileFactory;
use Phauthentic\Infrastructure\Storage\PathBuilder\PathBuilder;
use Phauthentic\Infrastructure\Storage\UrlBuilder\LocalUrlBuilder;
use Phauthentic\Test\TestCase\TestCase;

/**
 * Local Url BuilderTest
 */
class LocalUrlBuilderTest extends TestCase
{
    /**
     * @return void
     */
    public function testLocalUrlBuilder(): void
    {
        $pathBuilder = new PathBuilder();
        $urlBuilder = new LocalUrlBuilder('/', 'https');
        $fileOnDisk = $this->getFixtureFile('titus.jpg');
        $file = FileFactory::fromDisk($fileOnDisk, 'local')
            ->withUuid('914e1512-9153-4253-a81e-7ee2edc1d973')
            ->withFilename('foobar.jpg')
            ->addToCollection('avatar')
            ->belongsToModel('User', '1')
            ->buildPath($pathBuilder);

        $result = $urlBuilder->url($file);
        $this->assertEquals('/User/fe/c3/b4/914e151291534253a81e7ee2edc1d973/foobar.jpg', $result);
    }
}
