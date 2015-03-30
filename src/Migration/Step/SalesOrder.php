<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

use Migration\Exception;
use Migration\MapReader;
use Migration\App\Step\StepInterface;

/**
 * Class SalesOrder
 */
class SalesOrder implements StepInterface
{
    /**
     * @var SalesOrder\Integrity
     */
    protected $integrity;

    /**
     * @var SalesOrder\Migrate
     */
    protected $run;

    /**
     * @var SalesOrder\Volume
     */
    protected $volume;

    /**
     * @var SalesOrder\InitialData
     */
    protected $initialData;

    /**
     * @param SalesOrder\Integrity $integrity
     * @param SalesOrder\Migrate $run
     * @param SalesOrder\Volume $volume
     * @param SalesOrder\InitialData $initialData
     */
    public function __construct(
        SalesOrder\Integrity $integrity,
        SalesOrder\Migrate $run,
        SalesOrder\Volume $volume,
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
