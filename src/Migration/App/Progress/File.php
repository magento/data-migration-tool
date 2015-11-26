<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\App\Progress;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

/**
 * Class File
 */
class File
{
    /**
     * @var string
     */
    protected $lockFileName = 'migration-tool-progress.lock';

    /**
     * @var \Magento\Framework\Filesystem\DriverInterface
     */
    protected $filesystemDriver;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @param \Magento\Framework\Filesystem\Driver\File $filesystemDriver
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        \Magento\Framework\Filesystem\Driver\File $filesystemDriver,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->filesystemDriver = $filesystemDriver;
        $this->filesystem = $filesystem;
    }

    /**
     * Load progress from serialized file
     * @return bool|array
     */
    public function getData()
    {
        if (empty($this->data)) {
            $data = @unserialize($this->filesystemDriver->fileGetContents($this->getLockFile()));
            if (is_array($data)) {
                $this->data = $data;
            }
        }
        return $this->data;
    }

    /**
     * Writing data to lock file
     *
     * @param array $data
     * @return bool
     */
    public function saveData($data)
    {
        if ($this->filesystemDriver->isExists($this->getLockFile())) {
            $this->filesystemDriver->filePutContents($this->getLockFile(), serialize($data));
            $this->data = $data;
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    protected function getLockFile()
    {
        $lockFileDir = $this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR)->getAbsolutePath();
        $lockFile = $lockFileDir . DIRECTORY_SEPARATOR . $this->lockFileName;
        if (!$this->filesystemDriver->isExists($lockFile)) {
            $this->filesystemDriver->filePutContents($lockFile, 0);
        }
        return $lockFile;
    }

    /**
     * @return $this
     */
    public function clearLockFile()
    {
        $this->saveData([]);
        return $this;
    }
}
