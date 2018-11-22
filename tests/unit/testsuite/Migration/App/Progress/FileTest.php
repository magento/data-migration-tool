<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\App\Progress;

use Magento\Framework\App\Filesystem\DirectoryList;

class FileTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Filesystem\Driver\File|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystemDriver;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystem;

    /**
     * @var \Migration\App\Progress\File
     */
    protected $file;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->filesystemDriver = $this->getMockBuilder(\Magento\Framework\Filesystem\Driver\File::class)
            ->setMethods(['isExists', 'filePutContents', 'fileGetContents'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystemDriver->expects($this->any())->method('filePutContents')->will($this->returnValue(true));
        $directoryRead = $this->getMockBuilder(\Magento\Framework\Filesystem\Directory\ReadInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $directoryRead->expects($this->any())->method('getAbsolutePath')->willReturn('/path/to/var');
        $this->filesystem = $this->getMockBuilder(\Magento\Framework\Filesystem::class)
            ->setMethods(['getDirectoryRead'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystem->expects($this->any())->method('getDirectoryRead')->with(DirectoryList::VAR_DIR)
            ->willReturn($directoryRead);
        $this->file = new File($this->filesystemDriver, $this->filesystem);
    }

    /**
     * @return void
     */
    public function testSaveData()
    {
        $data = ['key' => ['other_key' => 'value']];
        $this->filesystemDriver->expects($this->any())->method('isExists')->will($this->returnValue(true));
        $this->assertTrue($this->file->saveData($data));
    }

    /**
     * @return void
     */
    public function testGetData()
    {
        $this->filesystemDriver->expects($this->any())->method('isExists')->will($this->returnValue(true));
        $dataSerialized = '{"object":{"integrity":true}}';
        $this->filesystemDriver->expects($this->once())->method('fileGetContents')->willReturn($dataSerialized);
        $data = $this->file->getData();
        $this->assertEquals(['object' => ['integrity' => true]], $data);
    }

    /**
     * @return void
     */
    public function testClearLockFile()
    {
        $this->file->clearLockFile();
        $this->assertEquals([], $this->file->getData());
    }
}
