<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

/**
 * Class Log
 */
class Log implements StepInterface
{
    /**
     * @var Integrity\Log
     */
    protected $integrityCheck;

    /**
     * @var Run\Log
     */
    protected $dataMigration;

    /**
     * @var Volume\Log
     */
    protected $volumeCheck;

    /**
     * @param Integrity\Log $integrity
     * @param Run\Log $dataMigration
     * @param Volume\Log $volumeCheck
     */
    public function __construct(
        Integrity\Log $integrity,
        Run\Log $dataMigration,
        Volume\Log $volumeCheck
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
