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
     * {@inheritdoc}
     */
    public function run()
    {
        parent::run();
        $currentProgress = $this->progress->getProgress();
        for ($i = $currentProgress; $i < $this->progress->getMaxSteps(); $i++) {
            $this->progress->advance();
            sleep(1);
        }
        $this->progress->finish();
        $this->logger->info('');
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxSteps()
    {
        return 15;
    }
}
