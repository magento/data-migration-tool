<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Migration\Logger;

class FileHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FileHandler
     */
    protected $fileHandler;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystem;

    /**
     * @var \Migration\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

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
    
    protected function setUp()
    {
        $this->filesystem = $this->getMockBuilder('Magento\Framework\Filesystem\Driver\File')
            ->disableOriginalConstructor()
            ->setMethods(['filePutContents', 'getRealPath', 'createDirectory'])
            ->getMock();
        $this->config = $this->getMockBuilder('Migration\Config')
            ->disableOriginalConstructor()
            ->setMethods(['getOption'])
            ->getMock();
        $this->fileHandler = new FileHandler($this->filesystem, $this->config);
    }

    public function testHandleSuccess()
    {
        $extra = ['mode' => 'application mode'];
        $record = ['message' => $this->message, 'level' => $this->recordLevel, 'extra' => $extra];
        $this->fileHandler->setLevel($this->handlerLevel);
        $file = 'file/path/file.log';
        $this->filesystem->expects($this->any())->method('filePutContents')->willReturn(1);
        $this->filesystem->expects($this->any())->method('getRealPath')->willReturnMap(
            [
                [$file, false],
                ['file/path', false],
                ['file', '/existing_path/file']
            ]
        );
        $this->filesystem->expects($this->once())->method('createDirectory')->willReturn(true);
        $this->config->expects($this->any())->method('getOption')->with('log_file')->willReturn($file);
        $result = $this->fileHandler->handle($record);
        $this->assertFalse($result);
    }

    public function testHandleSuccessWithoutBubble()
    {
        $extra = ['mode' => 'application mode'];
        $record = ['message' => $this->message, 'level' => $this->recordLevel, 'extra' => $extra];
        $this->fileHandler->setLevel($this->handlerLevel);
        $this->fileHandler->setBubble(false);
        $file = 'file/path/file.log';
        $this->filesystem->expects($this->any())->method('filePutContents')->willReturn(1);
        $this->filesystem->expects($this->any())->method('getRealPath')->willReturnMap(
            [
                [$file, false],
                ['file/path', false],
                ['file', '/existing_path/file']
            ]
        );
        $this->filesystem->expects($this->once())->method('createDirectory')->willReturn(true);
        $this->config->expects($this->any())->method('getOption')->with('log_file')->willReturn($file);
        $result = $this->fileHandler->handle($record);
        $this->assertTrue($result);
    }

    public function testHandleError()
    {
        $extra = ['mode' => 'application mode'];
        $record = ['message' => $this->message, 'level' => $this->recordLevel, 'extra' => $extra];
        $this->fileHandler->setLevel($this->handlerLevel);
        $result = $this->fileHandler->handle($record);
        $this->assertFalse($result);
    }
}
