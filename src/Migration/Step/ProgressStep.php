<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

/**
 * Class ProgressStep
 */
class ProgressStep
{
    /**
     * @var string
     */
    protected $lockFileName = 'migration-tool-progress.lock';

    /**
     * @var array
     */
    protected $data;

    /**
     * @var \Magento\Framework\Filesystem\DriverInterface
     */
    protected $filesystem;

    /**
     * @param \Magento\Framework\Filesystem\Driver\File $filesystem
     */
    public function __construct(
        \Magento\Framework\Filesystem\Driver\File $filesystem
    ) {
        $this->filesystem = $filesystem;
    }

    /**
     * Load step progress from serialized file
     * @return $this
     */
    protected function loadData()
    {
        if ($this->filesystem->isExists($this->getLockFile())) {
            $data = unserialize($this->filesystem->fileGetContents($this->getLockFile()));
            if (is_array($data)) {
                $this->data = $data;
            }
        }
        return $this;
    }

    /**
     * @param string $stepName
     * @param string $type
     * @param bool $result
     * @return $this
     */
    public function saveResult($stepName, $type, $result)
    {
        if ($this->filesystem->isExists($this->getLockFile())) {
            $this->data[$stepName][$type] = $result;
            $this->filesystem->filePutContents($this->getLockFile(), serialize($this->data));
        }
        return $this;
    }

    /**
     * @param string $stepName
     * @param string $type
     * @return bool
     */
    public function getResult($stepName, $type)
    {
        $this->loadData();
        $result = false;
        if (!empty($this->data[$stepName][$type])) {
            $result = $this->data[$stepName][$type] ? true : false;
        }
        return $result;
    }

    /**
     * @return string
     */
    protected function getLockFile()
    {
        return $this->lockFileName;
    }

    /**
     * @return $this
     */
    public  function clearLockFile()
    {
        if ($this->filesystem->isExists($this->getLockFile())) {
            $this->filesystem->filePutContents($this->getLockFile(), '');
        }
        return $this;
    }
}
