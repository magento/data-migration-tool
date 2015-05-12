<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\App;

use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ProgressBarFactory
 */
class ProgressBarFactory
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param OutputInterface $output
     * @return \Symfony\Component\Console\Helper\ProgressBar
     */
    public function create(OutputInterface $output)
    {
        return $this->objectManager->create(
            '\\Symfony\\Component\\Console\\Helper\\ProgressBar',
            ['output' => $output]
        );
    }
}
