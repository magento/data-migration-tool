<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

use \Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class Progress
 */
class Progress extends \Symfony\Component\Console\Helper\ProgressBar
{
    const IN_PROGRESS = 'in_progress';
    const FAILED = 'failed';
    const COMPLETED = 'completed';

    /**
     * @var string
     */
    protected $lockFileName = 'migration-tool-progress.lock';

    /**
     * @var array
     */
    protected $data;

    /**
     * @var StepInterface
     */
    protected $currentStep;

    /**
     * @var string
     */
    protected $currentStepClass;

    /**
     * @var \Magento\Framework\Filesystem\DriverInterface
     */
    protected $filesystem;

    /**
     * @param ConsoleOutput $output
     * @param \Magento\Framework\Filesystem\Driver\File $filesystem
     */
    public function __construct(
        ConsoleOutput $output,
        \Magento\Framework\Filesystem\Driver\File $filesystem
    ) {
        $this->filesystem = $filesystem;
        parent::__construct($output);
    }

    /**
     * @param StepInterface $step
     * @return $this
     */
    public function setStep(StepInterface $step)
    {
        $this->currentStep = $step;
        $this->currentStepClass = get_class($this->currentStep);
        if (!isset($this->data[$this->currentStepClass])) {
            $this->data[$this->currentStepClass] = [];
        }

        return $this;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function checkStep()
    {
        if (is_null($this->currentStep)) {
            throw new \Exception('Step is not specified');
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function start($max = null)
    {
        $this->checkStep();
        parent::start($max);
        $this->loadProgress();
        if (is_null($this->data)) {
            $this->saveProgress();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function advance($step = 1)
    {
        $this->checkStep();
        parent::advance($step);
        $this->saveProgress();
    }

    /**
     * {@inheritdoc}
     */
    public function finish()
    {
        $this->checkStep();
        parent::finish();
        $this->saveProgress(self::COMPLETED);
    }

    /**
     * Save progress as failed
     * @return $this
     */
    public function reset()
    {
        $this->checkStep();
        $this->data[$this->currentStepClass]['status'] = self::IN_PROGRESS;
        $this->data[$this->currentStepClass]['progress'] = 0;
        parent::setProgress($this->getStepProgress());
        $this->filesystem->filePutContents($this->getLockFile(), serialize($this->data));
        return $this;
    }

    /**
     * @param string $status
     * @return $this
     */
    protected function saveProgress($status = self::IN_PROGRESS)
    {
        $this->checkStep();
        $this->data[$this->currentStepClass]['status'] = $status;
        $this->data[$this->currentStepClass]['progress'] = $this->getProgress();
        $this->filesystem->filePutContents($this->getLockFile(), serialize($this->data));
        return $this;
    }

    /**
     * Load progress from serialized file
     * @return $this
     */
    protected function loadProgress()
    {
        if ($this->filesystem->isExists($this->getLockFile())) {
            $data = unserialize($this->filesystem->fileGetContents($this->getLockFile()));
            if (is_array($data)) {
                $this->data = $data;
            }
            parent::setProgress($this->getStepProgress());
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        $this->checkStep();
        return isset($this->data[$this->currentStepClass]) && isset($this->data[$this->currentStepClass]['status'])
            ? $this->data[$this->currentStepClass]['status']
            : null;
    }

    /**
     * @return int
     */
    public function getStepProgress()
    {
        $this->checkStep();
        return isset($this->data[$this->currentStepClass]) && isset($this->data[$this->currentStepClass]['progress'])
            ? $this->data[$this->currentStepClass]['progress']
            : 0;
    }

    /**
     * @return string
     */
    protected function getLockFile()
    {
        $lockFileDir = dirname(dirname(dirname(__DIR__))) . '/var';
        return $lockFileDir . DIRECTORY_SEPARATOR . $this->lockFileName;
    }
}
