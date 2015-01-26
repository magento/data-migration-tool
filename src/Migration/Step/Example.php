<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

/**
 * Class Example
 */
class Example extends AbstractStep
{
    /**
     * {@inherit_doc}
     */
    public function run()
    {
        parent::run();
        $status = $this->progress->getStatus();
        if ($status == Progress::COMPLETED) {
            $this->logger->logInfo("Step already completed. Skipped.");
            return;
        }
        if ($status && $status != Progress::COMPLETED) {
            $this->logger->logInfo('Step hasn\'t been completed. Trying to resume.');
        }
        $startPoint = $this->progress->getStepProgress() ? $this->progress->getStepProgress() : 0;
        $this->progress->start(15);
        $this->progress->setProgress($startPoint);
        for($i = $startPoint; $i < 14; $i++)
        {
            $this->progress->advance();
            sleep(1);
        }
        $this->progress->finish();
        $this->logger->log('');
    }
}
