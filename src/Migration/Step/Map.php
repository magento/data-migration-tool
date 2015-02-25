<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

use Migration\MapReader;
use Migration\Config;
use Migration\Logger\Logger;

/**
 * Class Map
 */
class Map implements StepInterface
{
    /**
     * @var Integrity\Map
     */
    protected $integrity;

    /**
     * @var Run\Map
     */
    protected $run;

    /**
     * @var Volume\Map
     */
    protected $volume;

    /**
     * @var MapReader
     */
    protected $map;

    /**
     * Logger instance
     *
     * @var Logger
     */
    protected $logger;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param Integrity\Map $integrity
     * @param Run\Map $run
     * @param Volume\Map $volume
     * @param Logger $logger
     * @param MapReader $mapReader
     * @param Config $config
     */
    public function __construct(
        Integrity\Map $integrity,
        Run\Map $run,
        Volume\Map $volume,
        Logger $logger,
        MapReader $mapReader,
        Config $config
    ) {
        $this->integrity = $integrity;
        $this->run = $run;
        $this->volume = $volume;
        $this->logger = $logger;
        $this->map = $mapReader;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function integrity()
    {
        try {
            $this->map->init($this->config->getOption('map_file'));
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
            $this->map->init($this->config->getOption('map_file'));
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
            $this->map->init($this->config->getOption('map_file'));
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
        return "Map step";
    }
}
