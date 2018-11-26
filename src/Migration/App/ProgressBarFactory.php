<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * Create
     *
     * @param OutputInterface $output
     * @return ProgressBar
     */
    public function create(OutputInterface $output)
    {
        return new ProgressBar($output);
    }
}
