<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\PostProcessing\Model;

use Migration\ResourceModel;
use Migration\Reader\GroupsFactory;
use Migration\Config;
use Magento\Framework\Module\ModuleListInterface;

/**
 * Class EavLeftoverData
 */
class AttributeSetLeftoverData
{
    /**
     * @var ResourceModel\Destination
     */
    private $destination;

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @var \Migration\Reader\Groups
     */
    private $readerDocument;

    /**
     * @var string
     */
    private $editionMigrate;

    /**
     * @param ResourceModel\Destination $destination
     * @param GroupsFactory $groupsFactory
     * @param Config $config
     */
    public function __construct(
        ResourceModel\Destination $destination,
        GroupsFactory $groupsFactory,
        Config $config,
        ModuleListInterface $moduleList
    ) {
        $this->destination = $destination;
        $this->readerDocument = $groupsFactory->create('eav_document_groups_file');
        $this->editionMigrate = $config->getOption('edition_migrate');
        $this->moduleList = $moduleList;
    }

    /**
     * Get leftover ids
     *
     * Returns ids of records which are still in product entity tables but product
     * attribute no longer exist in attribute set.
     *
     * @return array
     */
    public function getLeftoverIds()
    {
        $documents = [];
        $entityIdName = $this->editionMigrate == Config::EDITION_MIGRATE_OPENSOURCE_TO_OPENSOURCE
            || $this->moduleList->has('Magento_CatalogStaging') === false
            ? 'entity_id'
            : 'row_id';
        /** @var \Migration\ResourceModel\Adapter\Mysql $adapter */
        $adapter = $this->destination->getAdapter();
        $subSelect = $adapter->getSelect()->from(
            ['eea' => $this->destination->addDocumentPrefix('eav_entity_attribute')],
            ['attribute_id']
        )->where(
            'eea.attribute_set_id = cpe.attribute_set_id'
        );
        foreach ($this->getDocuments() as $document) {
            $select = $adapter->getSelect()->from(
                ['cpev' => $this->destination->addDocumentPrefix($document)],
                ['value_id']
            )->join(
                ['cpe' => $this->destination->addDocumentPrefix('catalog_product_entity')],
                'cpe.entity_id = cpev.' . $entityIdName,
                []
            )->where(
                'cpev.value IS NOT NULL'
            )->where('cpev.attribute_id NOT IN ?', $subSelect);
            if ($ids = $select->getAdapter()->fetchCol($select)) {
                $documents[$document] = $ids;
            }
        }
        return $documents;
    }

    /**
     * Get documents to check
     *
     * @return array
     */
    public function getDocuments()
    {
        return array_keys($this->readerDocument->getGroup('documents_attribute_set_leftover_values'));
    }
}
