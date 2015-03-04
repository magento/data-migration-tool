<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

use \Migration\Config;
use \Migration\MapReader;
use Migration\Logger\Logger;

/**
 * Class Log
 */
class Log implements StepInterface
{
    /**
     * @var Integrity\Log
     */
    protected $integrityCheck;

    /**
     * @var Run\Log
     */
    protected $dataMigration;

    /**
     * @var Volume\Log
     */
    protected $volumeCheck;

    /**
     * @var MapReader
     */
    protected $map;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param Integrity\Log $integrity
     * @param Run\Log $dataMigration
     * @param Volume\Log $volumeCheck
     * @param MapReader $mapReader
     * @param Config $config
     * @param Logger $logger
     */
    public function __construct(
        Integrity\Log $integrity,
        Run\Log $dataMigration,
        Volume\Log $volumeCheck,
        MapReader $mapReader,
        Config $config,
        Logger $logger
    ) {
        $this->integrityCheck = $integrity;
        $this->dataMigration = $dataMigration;
        $this->volumeCheck = $volumeCheck;
        $this->config = $config;
        $this->map = $mapReader;
        $this->logger = $logger;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function integrity()
    {
        try {
            $this->map->init($this->config->getOption('log_map_file'));
            return $this->integrityCheck->perform();
        } catch (\Exception $e) {
            $this->logger->error('Integrity check failed with exception: ' . $e->getMessage());
        }
        return false;
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function run()
    {
        try {
            $this->map->init($this->config->getOption('log_map_file'));
            $this->dataMigration->perform();
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Run failed with exception: ' . $e->getMessage());
        }
        return false;
    }

    /**
     * @return bool|int
     */
    public function volumeCheck()
    {
        try {
            $this->map->init($this->config->getOption('log_map_file'));
            return $this->volumeCheck->perform();
        } catch (\Exception $e) {
            $this->logger->error('Volume check failed with exception: ' . $e->getMessage());
        }
        return false;
    }

    /**
     * Get step title
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Log Step';
    }
}
