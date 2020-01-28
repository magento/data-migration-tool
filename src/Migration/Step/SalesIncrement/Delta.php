<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Step\SalesIncrement;

use Migration\App\Step\AbstractDelta;
use Migration\Logger\Logger;
use Migration\Reader\GroupsFactory;
use Migration\ResourceModel\Source;
use Migration\ResourceModel\Destination;
use Migration\Reader\MapFactory;
use Migration\ResourceModel;

/**
 * Class Delta
 */
class Delta extends AbstractDelta
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var string
     */
    protected $mapConfigOption = 'map_file';

    /**
     * @var string
     */
    protected $groupName = 'delta_sales_sequence';

    /**
     * @param Source $source
     * @param MapFactory $mapFactory
     * @param GroupsFactory $groupsFactory
     * @param Logger $logger
     * @param Destination $destination
     * @param ResourceModel\RecordFactory $recordFactory
     * @param \Migration\RecordTransformerFactory $recordTransformerFactory
     * @param Helper $helper
     */
    public function __construct(
        Source $source,
        MapFactory $mapFactory,
        GroupsFactory $groupsFactory,
        Logger $logger,
        Destination $destination,
        ResourceModel\RecordFactory $recordFactory,
        \Migration\RecordTransformerFactory $recordTransformerFactory,
        Helper $helper
    ) {
        $this->helper = $helper;
        parent::__construct(
            $source,
            $mapFactory,
            $groupsFactory,
            $logger,
            $destination,
            $recordFactory,
            $recordTransformerFactory
        );
    }

    /**
     * @inheritdoc
     */
    protected function transformData($data, $sourceDocument, $destDocument, $recordTransformer, $destinationRecords)
    {
        parent::transformData($data, $sourceDocument, $destDocument, $recordTransformer, $destinationRecords);
        /** @var \Magento\Framework\DB\Adapter\Pdo\Mysql $adapter */
        $adapter = $this->destination->getAdapter()->getSelect()->getAdapter();
        $entityType = $this->helper->getEntityTypeData('entity_type_id', $data['entity_type_id']);
        $incrementNumber = $this->helper->getIncrementForEntityType(
            $data['entity_type_id'],
            $data['store_id']
        );
        if ($incrementNumber === false || empty($entityType)) {
            return;
        }
        $tableName = $this->helper->getTableName($entityType['entity_type_table'], $data['store_id']);
        $adapter->insertOnDuplicate($tableName, [$entityType['column'] => $incrementNumber]);
    }
}
