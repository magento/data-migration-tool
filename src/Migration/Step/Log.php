<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

use Migration\Config;
use Migration\App\Step\DeltaInterface;

/**
 * Class Log
 */
class Log extends DatabaseStep implements DeltaInterface
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
     * @param Config $config
     * @param Integrity\Log $integrity
     * @param Run\Log $dataMigration
     * @param Volume\Log $volumeCheck
     */
    public function __construct(
        Config $config,
        Integrity\Log $integrity,
        Run\Log $dataMigration,
        Volume\Log $volumeCheck
    ) {
        parent::__construct($config);
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
    public function delta()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function setupTriggers()
    {
        return true;
    }
}
