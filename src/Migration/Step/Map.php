<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

use Migration\MapReader;
use Migration\Config;

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
     * @var Config
     */
    protected $config;

    /**
     * @param Integrity\Map $integrity
     * @param Run\Map $run
     * @param Volume\Map $volume
     * @param MapReader $mapReader
     * @param Config $config
     */
    public function __construct(
        Integrity\Map $integrity,
        Run\Map $run,
        Volume\Map $volume,
        MapReader $mapReader,
        Config $config
    ) {
        $this->integrity = $integrity;
        $this->run = $run;
        $this->volume = $volume;
        $this->map = $mapReader;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function integrity()
    {
        $this->map->init($this->config->getOption('map_file'));
        return $this->integrity->perform();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->map->init($this->config->getOption('map_file'));
        $this->run->perform();
    }

    /**
     * @inheritdoc
     */
    public function volumeCheck()
    {
        $this->map->init($this->config->getOption('map_file'));
        return $this->volume->perform();
    }
}
