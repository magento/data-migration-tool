<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

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
     * @param Map\Integrity $integrity
     * @param Map\Run $run
     * @param Map\Volume $volume
     */
    public function __construct(
        Map\Integrity $integrity,
        Map\Run $run,
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
        $this->run->perform();
    }

    /**
     * @inheritdoc
     */
    public function volumeCheck()
    {
        return $this->volume->perform();
    }
}
