<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\PostProcessing\Model;

use Migration\ResourceModel;
use Migration\Reader\GroupsFactory;
use Migration\Config;

/**
 * Class EavLeftoverData
 */
class EavLeftoverData
{
    /**
     * @var ResourceModel\Destination
     */
    private $destination;

    /**
     * @var \Migration\Reader\Groups
     */
    private $readerDocument;

    /**
     * @var string
     */
    private $editionMigrate = '';

    /**
     * @var string
     */
    private $eavAttributeDocument = 'eav_attribute';

    /**
     * @param ResourceModel\Destination $destination
     * @param GroupsFactory $groupsFactory
     * @param Config $config
     */
    public function __construct(
        ResourceModel\Destination $destination,
        GroupsFactory $groupsFactory,
        Config $config
    ) {
        $this->destination = $destination;
        $this->readerDocument = $groupsFactory->create('eav_document_groups_file');
        $this->editionMigrate = $config->getOption('edition_migrate');
    }

    /**
     * Return attribute ids which do not exist in 'eav_attribute' table but exist in reference tables
     *
     * @return array
     */
    public function getLeftoverAttributeIds()
    {
        /** @var \Migration\ResourceModel\Adapter\Mysql $adapter */
        $adapter = $this->destination->getAdapter();
        $selects = [];
        foreach ($this->getDocuments() as $document) {
            $selects[] = $adapter->getSelect()->from(
                ['ea' => $this->destination->addDocumentPrefix($this->eavAttributeDocument)],
                []
            )->joinRight(
                ['j' => $this->destination->addDocumentPrefix($document)],
                'j.attribute_id = ea.attribute_id',
                ['attribute_id']
            )->where(
                'ea.attribute_id IS NULL'
            )->group(
                'j.attribute_id'
            );
        }
        $query = $adapter->getSelect()->union($selects, \Zend_Db_Select::SQL_UNION);
        return $query->getAdapter()->fetchCol($query);
    }

    /**
     * Get documents
     *
     * @return array
     */
    public function getDocuments()
    {
        $documents = array_keys($this->readerDocument->getGroup('documents_leftover_values'));
        if ($this->editionMigrate != Config::EDITION_MIGRATE_OPENSOURCE_TO_OPENSOURCE) {
            $documents = array_merge(
                $documents,
                array_keys($this->readerDocument->getGroup('documents_leftover_values_ee'))
            );
        }
        return $documents;
    }
}
