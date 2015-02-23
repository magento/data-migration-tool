<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

use Migration\Logger\Logger;


class Map implements StepInterface
{
    /**
     * @var Map\Integrity
     */
    protected $integrity;

    /**
     * @var Map\Run
     */
    protected $run;

    /**
     * @var Map\Volume
     */
    protected $volume;

    /**
     * Logger instance
     *
     * @var Logger
     */
    protected $logger;

    /**
     * @param Map\Integrity $integrity
     * @param Map\Run $run
     * @param Map\Volume $volume
     * @param Logger $logger
     */
    public function __construct(
        Map\Integrity $integrity,
        Map\Run $run,
        Map\Volume $volume,
        Logger $logger

    ) {
        $this->integrity = $integrity;
        $this->run = $run;
        $this->volume = $volume;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function integrity()
    {
        try {
            return $this->integrity->perform();
        } catch (\Exception $e) {
            $this->logger->error('Integrity check failed with exception: ' . $e->getMessage());
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        try {
            $this->run->perform();
        } catch (\Exception $e) {
            $this->logger->error('Run failed with exception: ' . $e->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public function volumeCheck()
    {
        try {
            return $this->volume->perform();
        } catch (\Exception $e) {
            $this->logger->error('Volume check failed with exception: ' . $e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return "Map step: ";
    }
}
