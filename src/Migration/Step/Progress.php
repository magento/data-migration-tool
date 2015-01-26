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
     * @var \Magento\Framework\Filesystem\Directory\WriteFactory
     */
    protected $directoryWriteFactory;

    /**
     * @param \Migration\Config $config
     * @param ConsoleOutput $output
     * @param \Magento\Framework\Filesystem\Directory\WriteFactory $directoryWriteFactory
     */
    public function __construct(
        \Migration\Config $config,
        ConsoleOutput $output,
        \Magento\Framework\Filesystem\Directory\WriteFactory $directoryWriteFactory
    ) {
        $this->directoryWriteFactory = $directoryWriteFactory;
        parent::__construct($output);
        $this->loadProgress();
    }

    /**
     * {@inherit_doc}
     */
    public function start($max = null)
    {
        parent::start($max);
        $this->saveProgress();
    }

    /**
     * {@inherit_doc}
     */
    public function advance($step = 1)
    {
        parent::advance($step);
        $this->saveProgress();
    }

    /**
     * {@inherit_doc}
     */
    public function finish()
    {
        parent::finish();
        $this->saveProgress(self::COMPLETED);
    }

    /**
     * @param string $status
     * @return $this
     */
    protected function saveProgress($status = self::IN_PROGRESS)
    {
        $this->data[$this->currentStep]['status'] = $status;
        $this->data[$this->currentStep]['progress'] = $this->getProgress();
        file_put_contents($this->getLockFile(), serialize($this->data));
        return $this;
    }

    /**
     * Load progress from serialized file
     * @return $this
     */
    protected function loadProgress()
    {
        if (file_exists($this->getLockFile())) {
            $data = unserialize(file_get_contents($this->getLockFile()));
            if (is_array($data)) {
                $this->data = $data;
            }
        }
        return $this;
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
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return isset($this->data[$this->currentStep]) && isset($this->data[$this->currentStep]['status'])
            ? $this->data[$this->currentStep]['status']
            : null;
    }

    /**
     * @return int
     */
    public function getStepProgress()
    {
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
        if (!is_dir($lockFileDir)) {
            $this->directoryWriteFactory->create($lockFileDir)->create();
        }
        return $lockFileDir . DIRECTORY_SEPARATOR . $this->lockFileName;
    }

}
