<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\App;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ProgressBarFactory
 */
class ProgressBarFactory
{
    /**
     * @param OutputInterface $output
     * @return ProgressBar
     */
    public function create(OutputInterface $output)
    {
        return new ProgressBar($output);
    }
}
