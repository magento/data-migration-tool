<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\OrderGridsEE;

use Migration\ResourceModel;
use Migration\Logger\Logger;
use Migration\App\ProgressBar;

/**
 * Class Integrity
 */
class Integrity extends \Migration\Step\OrderGrids\Integrity
{
    /**
     * @param ProgressBar\LogLevelProcessor $progress
     * @param Logger $logger
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     * @param Helper $helper
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progress,
        Logger $logger,
        ResourceModel\Source $source,
        ResourceModel\Destination $destination,
        Helper $helper
    ) {
        parent::__construct($progress, $logger, $source, $destination, $helper);
    }
}
