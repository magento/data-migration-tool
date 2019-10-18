<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\SalesIncrement;

use Migration\App\Step\StageInterface;
use Migration\ResourceModel;
use Migration\ResourceModel\Document;
use Migration\ResourceModel\Record;
use Migration\App\ProgressBar;
use Migration\App\Progress;
use Migration\Logger\Manager as LogManager;

/**
 * Class Data
 */
class Data implements StageInterface
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var ResourceModel\Source
     */
    protected $source;

    /**
     * @var ResourceModel\Destination
     */
    protected $destination;

    /**
     * @var ProgressBar\LogLevelProcessor
     */
    protected $progressBar;

    /**
     * @var string
     */
    protected $storeTable = 'core_store';

    /**
     * @var string
     */
    protected $storeGroupTable = 'core_store_group';

    /**
     * @var string
     */
    protected $eavEntityStore = 'eav_entity_store';

    /**
     * @var array
     */
    protected $defaultValuesProfile = [
        'suffix' => '',
        'step' => 1,
        'start_value' => 1,
        'warning_value' => 4294966295,
        'max_value' => 4294967295,
        'is_active' => 1
    ];

    /**
     * @param ProgressBar\LogLevelProcessor $progressBar
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     * @param Helper $helper
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progressBar,
        ResourceModel\Source $source,
        ResourceModel\Destination $destination,
        Helper $helper
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->progressBar = $progressBar;
        $this->helper = $helper;
    }

    /**
     * @inheritdoc
     */
    public function perform()
    {
        $this->progressBar->start(1, LogManager::LOG_LEVEL_INFO);
        $this->progressBar->advance(LogManager::LOG_LEVEL_INFO);
        $this->destination->clearDocument($this->helper->getSequenceMetaTable());
        $this->destination->clearDocument($this->helper->getSequenceProfileTable());
        foreach ($this->helper->getEntityTypeTablesMap() as $entityType) {
            foreach ($this->helper->getStoreIds() as $storeId) {
                $this->createSequenceTable($entityType, $storeId);
                $metaId = $this->addDataMetaTable($entityType, $storeId);
                $this->addDataProfileTable($storeId, $metaId);
            }
        }
        $this->progressBar->finish(LogManager::LOG_LEVEL_INFO);
        return true;
    }

    /**
     * Create sequence table
     *
     * @param array $entityType
     * @param int $storeId
     * @return void
     */
    protected function createSequenceTable(array $entityType, $storeId)
    {
        /** @var \Magento\Framework\DB\Adapter\Pdo\Mysql $adapter */
        $adapter = $this->destination->getAdapter()->getSelect()->getAdapter();
        $tableName = $this->helper->getTableName($entityType['entity_type_table'], $storeId);
        $adapter->dropTable($tableName);
        $columnOptions = [
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
            'auto_increment' => true
        ];
        /** @var \Magento\Framework\DB\Ddl\Table $table */
        $table = $adapter->newTable($tableName)
            ->addColumn(
                $entityType['column'],
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                $columnOptions
            );
        $adapter->createTable($table);
        $incrementMaxNumber = $this->helper->getIncrementForEntityType($entityType['entity_type_id'], $storeId);
        if ($incrementMaxNumber !== false) {
            $adapter->insert($tableName, [$entityType['column'] => $incrementMaxNumber]);
        }
    }

    /**
     * Add data meta table
     *
     * @param array $entityType
     * @param int $storeId
     * @return int
     */
    protected function addDataMetaTable(array $entityType, $storeId)
    {
        $data = [
            'entity_type' => $entityType['entity_type_code'],
            'store_id' => $storeId,
            'sequence_table' => $this->helper->getTableName($entityType['entity_type_table'], $storeId),
        ];
        /** @var \Magento\Framework\DB\Adapter\Pdo\Mysql $adapter */
        $adapter = $this->destination->getAdapter()->getSelect()->getAdapter();
        $adapter->insert($this->helper->getTableName($this->helper->getSequenceMetaTable()), $data);
        return $adapter->lastInsertId($this->helper->getTableName($this->helper->getSequenceMetaTable()), 'meta_id');
    }

    /**
     * Add data profile table
     *
     * @param int $storeId
     * @param int $metaId
     * @return void
     */
    protected function addDataProfileTable($storeId, $metaId)
    {
        $data = [
            'meta_id' => $metaId,
            'prefix' => $storeId
        ];

        $data = array_merge($this->defaultValuesProfile, $data);
        $this->destination->saveRecords($this->helper->getSequenceProfileTable(), [$data]);
    }
}
