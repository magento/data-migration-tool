<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

use Migration\App\Step\StepInterface;

/**
 * Class Log
 */
class Log implements StepInterface
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
     * @param Log\Integrity $integrity
     * @param Log\Migrate $dataMigration
     * @param Log\Volume $volumeCheck
     */
    public function __construct(
        Log\Integrity $integrity,
        Log\Migrate $dataMigration,
        Log\Volume $volumeCheck
    ) {
        $this->integrityCheck = $integrity;
        $this->dataMigration = $dataMigration;
        $this->volumeCheck = $volumeCheck;
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
}
