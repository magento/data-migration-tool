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
    protected $progressFile = '/tmp/migration-tool-progress.log';

    /**
     * @var array
     */
    protected $data;

    /**
     * @var string
     */
    protected $currentStep;

    /**
     * @param \Migration\Config $config
     * @param \Symfony\Component\Console\Output\ConsoleOutput $output
     */
    public function __construct(\Migration\Config $config, ConsoleOutput $output)
    {
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
        file_put_contents($this->progressFile, serialize($this->data));
        return $this;
    }

    /**
     * Load progress from serialized file
     * @return $this
     */
    protected function loadProgress()
    {
        if (file_exists($this->progressFile)) {
            $data = unserialize(file_get_contents($this->progressFile));
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
}
