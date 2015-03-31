<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\App\Step;

/**
 * Class Progress
 */
class Progress
{
    /**
     * @var string
     */
    protected $lockFileName = 'migration-tool-progress.lock';

    /**
     * @var array
     */
    protected $data = [];

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
     * Load progress from serialized file
     * @return $this
     */
    protected function loadData()
    {
        if ($this->filesystem->isExists($this->getLockFile())) {
            $data = @unserialize($this->filesystem->fileGetContents($this->getLockFile()));
            if (is_array($data)) {
                $this->data = $data;
            }
        }
        return $this;
    }

    /**
     * @param mixed $object
     * @param string $stage
     * @param bool $result
     * @return $this
     */
    public function saveResult($object, $stage, $result)
    {
        $name = $this->getName($object);
        if ($this->filesystem->isExists($this->getLockFile())) {
            $this->data[$name][$stage] = $result;
            $this->filesystem->filePutContents($this->getLockFile(), serialize($this->data));
        }
        return $this;
    }

    /**
     * @param mixed $object
     * @param string $stage
     * @return bool
     */
    public function isCompleted($object, $stage)
    {
        $this->loadData();
        $name = $this->getName($object);
        return !empty($this->data[$name][$stage]);
    }

    /**
     * @param mixed $object
     * @return void
     */
    public function reset($object)
    {
        $this->loadData();
        if (!empty($this->data[$this->getName($object)])) {
            unset($this->data[$this->getName($object)]);
            $this->filesystem->filePutContents($this->getLockFile(), serialize($this->data));
        }
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
        $this->filesystem->filePutContents($this->getLockFile(), 0);
        return $this;
    }

    /**
     * @param mixed $object
     * @return null|string
     */
    protected function getName($object)
    {
        return get_class($object);
    }
}
