<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

use Migration\App\Step\DeltaInterface;
use Migration\App\Step\RollbackInterface;

/**
 * Class Log
 */
class Log implements DeltaInterface, RollbackInterface
{
    /**
     * @var Log\Integrity
     */
    protected $integrityCheck;

    /**
     * @var Log\Migrate
     */
    protected $dataMigration;

    /**
     * @var Log\Volume
     */
    protected $volumeCheck;

    /**
     * @var Log\Delta
     */
    protected $delta;

    /**
     * @param Log\Integrity $integrity
     * @param Log\Migrate $dataMigration
     * @param Log\Volume $volumeCheck
     * @param Log\Delta $delta
     */
    public function __construct(
        Log\Integrity $integrity,
        Log\Migrate $dataMigration,
        Log\Volume $volumeCheck,
        Log\Delta $delta
    ) {
        $this->integrityCheck = $integrity;
        $this->dataMigration = $dataMigration;
        $this->volumeCheck = $volumeCheck;
        $this->delta = $delta;
    }

    /**
     * @inheritdoc
     */
    public function integrity()
    {
        return $this->integrityCheck->perform();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->dataMigration->perform();
    }

    /**
     * @inheritdoc
     */
    public function volumeCheck()
    {
        return $this->volumeCheck->perform();
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return 'Log Step';
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
    public function delta()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function setUpChangeLog()
    {
        return $this->delta->setUpChangeLog();
    }
}
