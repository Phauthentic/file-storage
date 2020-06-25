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

namespace Phauthentic\Test\TestCase;

use Phauthentic\Infrastructure\Storage\FileFactory;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

/**
 * FileFactoryTest
 */
class FileFactoryTest extends TestCase
{
    /**
     * @return void
     */
    public function testInvalidUpload(): void
    {
        $uploadedFile = $this->getMockBuilder(UploadedFileInterface::class)
            ->getMock();

        $uploadedFile->expects($this->any())
            ->method('getError')
            ->willReturn(UPLOAD_ERR_NO_FILE);

        $this->expectException(RuntimeException::class);
        FileFactory::fromUploadedFile($uploadedFile, 'local');
    }

    /**
     * @return void
     */
    public function testValidUpload(): void
    {
        $stream = $this->getMockBuilder(StreamInterface::class)
            ->getMock();

        $uploadedFile = $this->getMockBuilder(UploadedFileInterface::class)
            ->getMock();

        $uploadedFile->expects($this->any())
            ->method('getError')
            ->willReturn(UPLOAD_ERR_OK);

        $uploadedFile->expects($this->any())
            ->method('getClientFilename')
            ->willReturn('titus.jpg');

        $uploadedFile->expects($this->any())
            ->method('getSize')
            ->willReturn(12345);

        $uploadedFile->expects($this->any())
            ->method('getClientMediaType')
            ->willReturn('image/image-jpg');

        $uploadedFile->expects($this->any())
            ->method('getStream')
            ->willReturn($stream);

        //$file = FileFactory::fromUploadedFile($uploadedFile, 'local');

        //$this->assertInstanceOf(FileInterface::class, $file);
    }
}
