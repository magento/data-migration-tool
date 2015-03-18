<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

use Migration\App\Step\StepInterface;

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
     * @param Integrity\Map $integrity
     * @param Run\Map $run
     * @param Volume\Map $volume
     */
    public function __construct(
        Integrity\Map $integrity,
        Run\Map $run,
        Volume\Map $volume
    ) {
        $this->integrity = $integrity;
        $this->run = $run;
        $this->volume = $volume;
    }

    /**
     * @inheritdoc
     */
    public function integrity()
    {
        return $this->integrity->perform();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->run->perform();
    }

    /**
     * @inheritdoc
     */
    public function volumeCheck()
    {
        return $this->volume->perform();
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return "Map step";
    }

    /**
     * @inheritdoc
     */
    public function rollback()
    {
        return true;
    }
}
