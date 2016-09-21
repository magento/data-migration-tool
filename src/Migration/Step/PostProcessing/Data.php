<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\PostProcessing;

use Migration\App\Step\StageInterface;
use Migration\App\ProgressBar;
use Migration\Logger\Manager as LogManager;
use Migration\Step\PostProcessing\Data\EavLeftoverDataCleaner;

/**
 * Class Data
 */
class Data implements StageInterface
{
    /**
     * @var ProgressBar\LogLevelProcessor
     */
    private $progressBar;

    /**
     * @var EavLeftoverDataCleaner
     */
    private $eavLeftoverDataCleaner;

    /**
     * @param ProgressBar\LogLevelProcessor $progressBar
     * @param EavLeftoverDataCleaner $eavLeftoverDataCleaner
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progressBar,
        EavLeftoverDataCleaner $eavLeftoverDataCleaner
    ) {
        $this->progressBar = $progressBar;
        $this->eavLeftoverDataCleaner = $eavLeftoverDataCleaner;
    }

    /**
     * @return bool
     */
    public function perform()
    {
        $this->progressBar->start($this->getIterationsCount(), LogManager::LOG_LEVEL_INFO);
        $this->eavLeftoverDataCleaner->clean();
        $this->progressBar->finish(LogManager::LOG_LEVEL_INFO);
        return true;
    }

    /**
     * @return int
     */
    private function getIterationsCount()
    {
        return $this->eavLeftoverDataCleaner->getIterationsCount();
    }
}
