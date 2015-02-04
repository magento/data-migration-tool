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
     * @var string
     */
    protected $currentStep;

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
        $this->loadProgress();
    }

    /**
     * @param StepInterface $step
     * @return $this
     */
    public function setStep(StepInterface $step)
    {
        $this->currentStep = get_class($step);
        if (!isset($this->data[$this->currentStep])) {
            $this->data[$this->currentStep] = [];
        }

        parent::start($step->getMaxSteps());
        parent::setProgress($this->getStepProgress());

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
        $this->saveProgress();
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
    public function fail()
    {
        $this->checkStep();
        $this->saveProgress(self::FAILED);
        return $this;
    }

    /**
     * @param string $status
     * @return $this
     */
    protected function saveProgress($status = self::IN_PROGRESS)
    {
        $this->checkStep();
        $this->data[$this->currentStep]['status'] = $status;
        $this->data[$this->currentStep]['progress'] = $status == self::FAILED ? 0 : $this->getProgress();
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
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        $this->checkStep();
        return isset($this->data[$this->currentStep]) && isset($this->data[$this->currentStep]['status'])
            ? $this->data[$this->currentStep]['status']
            : null;
    }

    /**
     * @return int
     */
    public function getStepProgress()
    {
        $this->checkStep();
        return isset($this->data[$this->currentStep]) && isset($this->data[$this->currentStep]['progress'])
            ? $this->data[$this->currentStep]['progress']
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
