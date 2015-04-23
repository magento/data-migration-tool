<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\App\Progress;

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
    protected $filesystem;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @param \Magento\Framework\Filesystem\Driver\File $filesystem
     */
    public function __construct(
        \Magento\Framework\Filesystem\Driver\File $filesystem
    ) {
        $this->filesystem = $filesystem;
    }

    /**
     * Load progress from serialized file
     * @return bool|array
     */
    public function getData()
    {
        if (empty($this->data)) {
            $data = @unserialize($this->filesystem->fileGetContents($this->getLockFile()));
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
        if ($this->filesystem->isExists($this->getLockFile())) {
            $this->filesystem->filePutContents($this->getLockFile(), serialize($data));
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
        $lockFileDir = dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR .'var';
        $lockFile = $lockFileDir . DIRECTORY_SEPARATOR . $this->lockFileName;
        if (!$this->filesystem->isExists($lockFile)) {
            $this->filesystem->filePutContents($lockFile, 0);
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
