<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

use Migration\App\Step\RollbackInterface;

/**
 * Class Eav
 */
class Eav implements RollbackInterface
{
    /**
     * @var Eav\Integrity
     */
    protected $integrityCheck;

    /**
     * @var Eav\Data
     */
    protected $dataMigration;

    /**
     * @var Eav\Volume
     */
    protected $volumeCheck;

    /**
     * @var Eav\InitialData
     */
    protected $initialData;

    /**
     * @param Eav\InitialData $initialData
     * @param Eav\Integrity $integrity
     * @param Eav\Data $dataMigration
     * @param Eav\Volume $volumeCheck
     */
    public function __construct(
        Eav\InitialData $initialData,
        Eav\Integrity $integrity,
        Eav\Data $dataMigration,
        Eav\Volume $volumeCheck
    ) {
        $this->initialData = $initialData;
        $this->integrityCheck = $integrity;
        $this->dataMigration = $dataMigration;
        $this->volumeCheck = $volumeCheck;
    }

    /**
     * @return bool
     */
    public function integrity()
    {
        return $this->integrityCheck->perform();
    }

    /**
     * {@inheritdoc}
     */
    public function perform()
    {
        $this->initialData->init();
        return $this->dataMigration->perform();
    }

    /**
     * @inheritdoc
     */
    public function volumeCheck()
    {
        $result = $this->volumeCheck->perform();
        if ($result) {
            $this->dataMigration->deleteBackups();
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function rollback()
    {
        $this->dataMigration->rollback();
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return 'EAV Step';
    }
}
