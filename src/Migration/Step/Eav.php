<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

/**
 * Class Eav
 */
class Eav implements StepInterface
{
    /**
     * @var Integrity\Eav
     */
    protected $integrityCheck;

    /**
     * @var Run\Eav
     */
    protected $dataMigration;

    /**
     * @var Volume\Eav
     */
    protected $volumeCheck;

    /**
     * @var Eav\InitialData
     */
    protected $initialData;

    /**
     * @param Eav\InitialData $initialData
     * @param Integrity\Eav $integrity
     * @param Run\Eav $dataMigration
     * @param Volume\Eav $volumeCheck
     */
    public function __construct(
        Eav\InitialData $initialData,
        Integrity\Eav $integrity,
        Run\Eav $dataMigration,
        Volume\Eav $volumeCheck
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
    public function run()
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
