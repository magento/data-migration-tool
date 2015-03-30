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
     * @param Map\Integrity $integrity
     * @param Map\Migrate $run
     * @param Map\Volume $volume
     */
    public function __construct(
        Map\Integrity $integrity,
        Map\Migrate $run,
        Map\Volume $volume
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
