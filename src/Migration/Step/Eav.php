<?php
/**
 * Copyright © 2015 Ma nto. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

use \Migration\Config;
use \Migration\MapReader;
use Migration\Logger\Logger;

/**
 * Class Eav
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Eav implements StepInterface
{
    /**
     * @var Integrity\Eav
     */
    protected $integrityCheck;

    /**
     * @var Run\Eav
     */
    protected $dataMigration;

    /**
     * @var Volume\Eav
     */
    protected $volumeCheck;

    /**
     * @var Eav\InitialData
     */
    protected $initialData;

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
     * @param Eav\InitialData $initialData
     * @param Integrity\Eav $integrity
     * @param Run\Eav $dataMigration
     * @param Volume\Eav $volumeCheck
     * @param MapReader $mapReader
     * @param Config $config
     */
    public function __construct(
        Eav\InitialData $initialData,
        Integrity\Eav $integrity,
        Run\Eav $dataMigration,
        Volume\Eav $volumeCheck,
        MapReader $mapReader,
        Config $config,
        Logger $logger
    ) {
        $this->initialData = $initialData;
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
        try{
            $this->map->init($this->config->getOption('eav_map_file'));
            $this->initialData->init();
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
            $this->map->init($this->config->getOption('eav_map_file'));
            $this->dataMigration->perform();
        } catch (\Exception $e) {
            $this->logger->error('Run failed with exception: ' . $e->getMessage());
        }
    }

    /**
     * @return bool|int
     */
    public function volumeCheck()
    {
        try {
            $this->map->init($this->config->getOption('eav_map_file'));
            return $this->volumeCheck->perform();
        } catch (\Exception $e) {
            $this->logger->error('Volume check failed with exception: ' . $e->getMessage());
        }
    }

    /**
     * Get step title
     *
     * @return string
     */
    public function getTitle()
    {
        return 'EAV Step';
    }
}
