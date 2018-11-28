<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\OrderGridsEE;

use Migration\Logger\Logger;
use Migration\ResourceModel;
use Migration\App\ProgressBar;

/**
 * Class Volume
 */
class Volume extends \Migration\Step\OrderGrids\Volume
{
    /**
     * @param Logger $logger
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     * @param ProgressBar\LogLevelProcessor $progressBar
     * @param Helper $helper
     */
    public function __construct(
        Logger $logger,
        ResourceModel\Source $source,
        ResourceModel\Destination $destination,
        ProgressBar\LogLevelProcessor $progressBar,
        Helper $helper
    ) {
        parent::__construct($logger, $source, $destination, $progressBar, $helper);
    }
}
