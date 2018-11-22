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
 * The class is responsible for counting records of source documents
 */
class SourceRecordsCounter
{
    /**
     * @var ResourceModel\Source
     */
    private $source;

    /**
     * @var AttributesDataToSkip
     */
    private $attributesDataToSkip;

    /**
     * @var array
     */
    private $sourceEntityDocuments = [];

    /**
     * @param ResourceModel\Source $source
     * @param AttributesDataToSkip $attributesDataToSkip
     * @param GroupsFactory $groupsFactory
     * @param EntityTypeCode $entityTypeCode
     */
    public function __construct(
        ResourceModel\Source $source,
        AttributesDataToSkip $attributesDataToSkip,
        GroupsFactory $groupsFactory,
        EntityTypeCode $entityTypeCode
    ) {
        $this->source = $source;
        $this->attributesDataToSkip = $attributesDataToSkip;
        $entityDocuments = $groupsFactory->create('customer_document_groups_file')->getGroup('source_entity_documents');
        $this->sourceEntityDocuments = array_keys($entityDocuments);
        $this->entityTypeCode = $entityTypeCode;
    }

    /**
     * Count records of given source document
     *
     * @param string $document
     * @return int
     */
    public function getRecordsCount($document)
    {
        if (in_array($document, $this->sourceEntityDocuments)) {
            return $this->source->getRecordsCount($document);
        }
        $skipAttributes = $this->attributesDataToSkip->getSkippedAttributes($document);
        /** @var Mysql $adapter */
        $adapter = $this->source->getAdapter();
        $query = $adapter->getSelect()
            ->from(
                [
                    'et' => $this->source->addDocumentPrefix($document)
                ],
                'COUNT(*)'
            )
            ->where('et.attribute_id NOT IN (?)', array_keys($skipAttributes));
        $count = $query->getAdapter()->fetchOne($query);
        return $count;
    }
}
