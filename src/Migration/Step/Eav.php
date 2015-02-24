<?php
/**
 * Copyright © 2015 Ma nto. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

use \Migration\Config;
use \Migration\MapReader;

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
        Config $config
    ) {
        $this->initialData = $initialData;
        $this->integrityCheck = $integrity;
        $this->dataMigration = $dataMigration;
        $this->volumeCheck = $volumeCheck;
        $this->config = $config;
        $this->map = $mapReader;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function integrity()
    {
        $this->map->init($this->config->getOption('eav_map_file'));
        $this->initialData->init();
        return $this->integrityCheck->perform();
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function run()
    {
        $this->map->init($this->config->getOption('eav_map_file'));
        $this->dataMigration->perform();
    }

    /**
     * @return bool|int
     */
    public function volumeCheck()
    {
        $this->map->init($this->config->getOption('eav_map_file'));
        return $this->volumeCheck->perform();
    }


}
