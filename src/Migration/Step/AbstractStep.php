<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

use Migration\Logger\Logger;

/**
 * Class AbstractStep
 */
class AbstractStep implements StepInterface
{
    /**
     * @var Progress
     */
    protected $progress;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param Progress $progress
     * @param Logger $logger
     */
    public function __construct(Progress $progress, Logger $logger)
    {
        $this->logger = $logger;
        $this->progress = $progress;
    }

    /**
     * {@inherit_doc}
     */
    public function run()
    {
        $this->progress->setStep($this);
    }
}
