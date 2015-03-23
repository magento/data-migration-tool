<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

use Migration\App\Step\StepInterface;
use Migration\App\Step\DeltaInterface;

/**
 * Class Map
 */
class Map implements StepInterface, DeltaInterface
{
    /**
     * @var Map\Integrity
     */
    protected $integrity;

    /**
     * @var Map\Migrate
     */
    protected $run;

    /**
     * @var Map\Volume
     */
    protected $volume;

    /**
     * @var Map\Delta
     */
    protected $delta;

    /**
     * @param Map\Integrity $integrity
     * @param Map\Migrate $run
     * @param Map\Volume $volume
     * @param Map\Delta $delta
     */
    public function __construct(
        Map\Integrity $integrity,
        Map\Migrate $run,
        Map\Volume $volume,
        Map\Delta $delta
    ) {
        $this->integrity = $integrity;
        $this->run = $run;
        $this->volume = $volume;
        $this->delta = $delta;
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


    /**
     * @inheritdoc
     */
    public function setupDelta()
    {
        if ($this->delta->setUpDelta()) {
            return true;
        }
        return false;
    }
}
