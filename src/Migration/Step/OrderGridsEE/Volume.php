<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\OrderGridsEE;

use Migration\Logger\Logger;
use Migration\Resource;
use Migration\App\ProgressBar;

class Volume extends \Migration\Step\OrderGrids\Volume
{
    /**
     * @param Logger $logger
     * @param Resource\Source $source
     * @param Resource\Destination $destination
     * @param ProgressBar\LogLevelProcessor $progressBar
     * @param Helper
     */
    public function __construct(
        Logger $logger,
        Resource\Source $source,
        Resource\Destination $destination,
        ProgressBar\LogLevelProcessor $progressBar,
        Helper $helper
    ) {
        parent::__construct($logger, $source, $destination, $progressBar, $helper);
    }
}
