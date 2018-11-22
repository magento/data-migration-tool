<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Logger;

use Magento\Framework\App\Filesystem\DirectoryList;

class FileHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var FileHandler
     */
    protected $fileHandler;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $file;

    /**
     * @var \Migration\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystem;

    /**
     * @var string
     */
    protected $message = 'Text message';

    /**
     * @var int
     */
    protected $recordLevel = 1;

    /**
     * @var int
     */
    protected $handlerLevel = 1;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->file = $this->getMockBuilder(\Magento\Framework\Filesystem\Driver\File::class)
            ->disableOriginalConstructor()
            ->setMethods(['filePutContents', 'getRealPath', 'createDirectory'])
            ->getMock();
        $this->config = $this->getMockBuilder(\Migration\Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOption'])
            ->getMock();
        $directoryRead = $this->getMockBuilder(\Magento\Framework\Filesystem\Directory\ReadInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $directoryRead->expects($this->any())->method('getAbsolutePath')->willReturn('/path/to/var');
        $this->filesystem = $this->getMockBuilder(\Magento\Framework\Filesystem::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDirectoryRead'])
            ->getMock();
        $this->filesystem->expects($this->any())->method('getDirectoryRead')->with(DirectoryList::VAR_DIR)
            ->willReturn($directoryRead);
        $this->fileHandler = new FileHandler($this->file, $this->config, $this->filesystem);
    }

    /**
     * @return void
     */
    public function testHandleSuccess()
    {
        $extra = ['mode' => 'application mode'];
        $context = [];
        $record = [
            'message' => $this->message,
            'level' => $this->recordLevel,
            'extra' => $extra,
            'context' => $context
        ];
        $this->fileHandler->setLevel($this->handlerLevel);
        $file = 'file/path/file.log';
        $this->file->expects($this->any())->method('filePutContents')->willReturn(1);
        $this->file->expects($this->any())->method('getRealPath')->willReturnMap(
            [
                [$file, false],
                ['file/path', false],
                ['file', '/existing_path/file']
            ]
        );
        $this->file->expects($this->once())->method('createDirectory')->willReturn(true);
        $this->config->expects($this->any())->method('getOption')->with('log_file')->willReturn($file);
        $result = $this->fileHandler->handle($record);
        $this->assertFalse($result);
    }

    /**
     * @return void
     */
    public function testHandleSuccessWithoutBubble()
    {
        $extra = ['mode' => 'application mode'];
        $context = [];
        $record = [
            'message' => $this->message,
            'level' => $this->recordLevel,
            'extra' => $extra,
            'context' => $context
        ];
        $this->fileHandler->setLevel($this->handlerLevel);
        $this->fileHandler->setBubble(false);
        $file = 'file/path/file.log';
        $this->file->expects($this->any())->method('filePutContents')->willReturn(1);
        $this->file->expects($this->any())->method('getRealPath')->willReturnMap(
            [
                [$file, false],
                ['file/path', false],
                ['file', '/existing_path/file']
            ]
        );
        $this->file->expects($this->once())->method('createDirectory')->willReturn(true);
        $this->config->expects($this->any())->method('getOption')->with('log_file')->willReturn($file);
        $result = $this->fileHandler->handle($record);
        $this->assertTrue($result);
    }

    /**
     * @return void
     */
    public function testHandleError()
    {
        $extra = ['mode' => 'application mode'];
        $context = [];
        $record = [
            'message' => $this->message,
            'level' => $this->recordLevel,
            'extra' => $extra,
            'context' => $context
        ];
        $this->fileHandler->setLevel($this->handlerLevel);
        $result = $this->fileHandler->handle($record);
        $this->assertFalse($result);
    }
}
