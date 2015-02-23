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
     * Load step progress from serialized file
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
     * @param \Migration\Step\StepInterface $step
     * @param string $stage
     * @param bool $result
     * @return $this
     */
    public function saveResult(StepInterface $step, $stage, $result)
    {
        $stepName = $this->getStepName($step);
        if ($this->filesystem->isExists($this->getLockFile())) {
            $this->data[$stepName][$stage] = $result;
            $this->filesystem->filePutContents($this->getLockFile(), serialize($this->data));
        }
        return $this;
    }

    /**
     * @param \Migration\Step\StepInterface $step
     * @param string $stage
     * @return bool
     */
    public function isCompleted(StepInterface $step, $stage)
    {
        $this->loadData();
        $stepName = $this->getStepName($step);
        return !empty($this->data[$stepName][$stage]);
    }

    /**
     * @return string
     */
    protected function getLockFile()
    {
        $lockFileDir = dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR .'var';
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
     * @param \Migration\Step\StepInterface $step
     * @return null|string
     */
    protected function getStepName(StepInterface $step)
    {
        $stepName = get_class($step);
        return $stepName;
    }
}
