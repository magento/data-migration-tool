<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Customer\Model;

use Migration\ResourceModel\Adapter\Mysql;
use Migration\ResourceModel;
use Migration\Reader\GroupsFactory;

/**
 * The class is responsible for marked customer attributes as static
 */
class AttributesToStatic
{
    /**
     * @var ResourceModel\Destination
     */
    private $destination;

    /**
     * @var \Migration\Reader\Groups
     */
    private $readerGroups;

    /**
     * @var \Migration\Reader\Groups
     */
    private $readerAttributes;

    /**
     * @var EntityTypeCode
     */
    private $entityTypeCode;

    /**
     * @param ResourceModel\Destination $destination
     * @param GroupsFactory $groupsFactory
     * @param EntityTypeCode $entityTypeCode
     */
    public function __construct(
        ResourceModel\Destination $destination,
        GroupsFactory $groupsFactory,
        EntityTypeCode $entityTypeCode
    ) {
        $this->destination = $destination;
        $this->readerAttributes = $groupsFactory->create('customer_attribute_groups_file');
        $this->readerGroups = $groupsFactory->create('customer_document_groups_file');
        $this->entityTypeCode = $entityTypeCode;
    }

    /**
     * Update
     *
     * @throws \Zend_Db_Adapter_Exception
     * @return void
     */
    public function update()
    {
        /** @var Mysql $adapter */
        $adapter = $this->destination->getAdapter();
        $query = $adapter->getSelect()
            ->from($this->destination->addDocumentPrefix('eav_entity_type'), ['entity_type_id', 'entity_type_code']);
        $entityTypes = $query->getAdapter()->fetchAll($query);
        $entityTypesByCode = [];
        foreach ($entityTypes as $entityType) {
            $entityTypesByCode[$entityType['entity_type_code']] = $entityType['entity_type_id'];
        }

        $entityDocuments = array_keys($this->readerGroups->getGroup('source_entity_documents'));
        foreach ($entityDocuments as $entityDocument) {
            $entityTypeCode = $this->entityTypeCode->getEntityTypeCodeByDocumentName($entityDocument);
            $codes = implode("','", array_keys($this->readerAttributes->getGroup($entityDocument)));
            $where = [
                sprintf("attribute_code IN ('%s')", $codes),
                sprintf("entity_type_id = '%s'", $entityTypesByCode[$entityTypeCode])
            ];
            $adapter->getSelect()->getAdapter()->update(
                $this->destination->addDocumentPrefix('eav_attribute'),
                ['backend_type' => 'static'],
                $where
            );
        }
    }
}
