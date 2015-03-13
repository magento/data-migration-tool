<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

use Migration\Exception;
use Migration\MapReader;

/**
 * Class Map
 */
class SalesOrder implements StepInterface
{
    /**
     * @var Integrity\SalesOrder
     */
    protected $integrity;

    /**
     * @var Run\SalesOrder
     */
    protected $run;

    /**
     * @var Volume\SalesOrder
     */
    protected $volume;

    /**
     * @var SalesOrder\InitialData
     */
    protected $initialData;

    /**
     * @param Integrity\SalesOrder $integrity
     * @param Run\SalesOrder $run
     * @param Volume\SalesOrder $volume
     * @param SalesOrder\InitialData $initialData
     */
    public function __construct(
        Integrity\SalesOrder $integrity,
        Run\SalesOrder $run,
        Volume\SalesOrder $volume,
        SalesOrder\InitialData $initialData
    ) {
        $this->integrity = $integrity;
        $this->run = $run;
        $this->volume = $volume;
        $this->initialData = $initialData;
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
        $this->initialData->init();
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
        return "SalesOrder step";
    }

    /**
     * @inheritdoc
     */
    public function rollback()
    {
        throw new Exception('Rollback is impossible for ' . $this->getTitle());
    }
}
