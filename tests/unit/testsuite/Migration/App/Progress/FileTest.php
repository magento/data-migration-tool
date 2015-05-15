<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\App\Progress;

class FileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Filesystem\Driver\File|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystem;

    /**
     * @var \Migration\App\Progress\File
     */
    protected $file;

    public function setUp()
    {
        $this->filesystem = $this->getMockBuilder('\Magento\Framework\Filesystem\Driver\File')
            ->setMethods(['isExists', 'filePutContents', 'fileGetContents'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystem->expects($this->any())->method('filePutContents')->will($this->returnValue(true));
        $this->file = new File($this->filesystem);
    }

    public function testSaveData()
    {
        $data = ['key' => ['other_key' => 'value']];
        $this->filesystem->expects($this->any())->method('isExists')->will($this->returnValue(true));
        $this->file->saveData($data);
    }

    public function testGetData()
    {
        $this->filesystem->expects($this->any())->method('isExists')->will($this->returnValue(true));
        $dataSerialized = 'a:1:{s:6:"object";a:1:{s:9:"integrity";b:1;}}';
        $this->filesystem->expects($this->once())->method('fileGetContents')->will($this->returnValue($dataSerialized));
        $data = $this->file->getData();
        $this->assertEquals(['object' => ['integrity' => true]], $data);
    }

    public function testClearLockFile()
    {
        $this->file->clearLockFile();
        $this->assertEquals([], $this->file->getData());
    }
}
