<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Step\VisualMerchandiser;

use Migration\ResourceModel\Adapter\Mysql;
use Migration\ResourceModel;
use Migration\Reader\GroupsFactory;
use Migration\ResourceModel\Record;

/**
 * Class Helper
 */
class Helper
{
    const DEFAULT_STORE_ID = 0;

    /**
     * @var []
     */
    protected $attributeMapping = [
        'none' => '0',
        'instock_at_top' => '2',
        'special_at_top' => '3',
        'special_at_bottom' => '4'
    ];

    /**
     * @var []
     */
    protected $documentAttributeTypes;

    /**
     * @var ResourceModel\Source
     */
    protected $source;

    /**
     * @var []
     */
    public $records = [];

    /**
     * @var ResourceModel\Destination
     */
    protected $destination;

    /**
     * @var \Migration\Reader\Groups
     */
    protected $readerAttributes;

    /**
     * @var \Migration\Reader\Groups
     */
    protected $readerGroups;

    /**
     * @var []
     */
    protected $sourceDocuments;

    /**
     * @var []
     */
    protected $eavAttributes;

    /**
     * @var []
     */
    protected $skipAttributes;

    /**
     * @var string
     */
    protected $entityName;

    /**
     * @var ResourceModel\RecordFactory
     */
    protected $recordFactory;

    /**
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     * @param GroupsFactory $groupsFactory
     * @param ResourceModel\RecordFactory $recordFactory
     */
    public function __construct(
        ResourceModel\Source $source,
        ResourceModel\Destination $destination,
        GroupsFactory $groupsFactory,
        ResourceModel\RecordFactory $recordFactory
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->readerAttributes = $groupsFactory->create('visual_merchandiser_attribute_groups');
        $this->readerGroups = $groupsFactory->create('visual_merchandiser_document_groups');
        $this->sourceDocuments = $this->readerGroups->getGroup('source_documents');
        $this->recordFactory = $recordFactory;
    }

    /**
     * Get document list
     *
     * @return array
     */
    public function getDocumentList()
    {
        return [
            'merchandiser_vmbuild' => 'visual_merchandiser_product_attribute_idx'
        ];
    }

    /**
     * Update attribute data
     *
     * @param array $recordData
     * @return void
     */
    public function updateAttributeData($recordData)
    {
        $attributeCodes = array_keys($this->readerAttributes->getGroup($this->entityName));

        $sourceData['attribute_id'] = $this->eavAttributes['catalog_category']['automatic_sorting']['attribute_id'];
        $sourceData['row_id'] = $recordData['category_id'];
        $sourceData['store_id'] = self::DEFAULT_STORE_ID;
        $sourceData['value'] = (array_key_exists($recordData['automatic_sort'], $this->attributeMapping)) ?
            $this->attributeMapping[$recordData['automatic_sort']] :
            '0';

        $data = array_merge(
            array_fill_keys($attributeCodes, null),
            $sourceData
        );

        $destDocument = $this->destination->getDocument($this->entityName);
        $destRecord = $this->recordFactory->create(['document' => $destDocument]);

        $destRecord->setData($data);
        $this->records[] = $destRecord;
    }

    /**
     * Saves the records
     *
     * @return void
     */
    public function saveRecords()
    {
        $this->destination->saveRecords($this->entityName, $this->records);
    }

    /**
     * Init eav entity
     *
     * @param string $attributeType
     * @param string $document
     * @param string $key
     * @return void
     * @throws \Migration\Exception
     */
    protected function initEavEntity($attributeType, $document, $key)
    {
        if ($key != 'entity_id') {
            return;
        }
        $this->initEavAttributes($attributeType);
        foreach (array_keys($this->readerAttributes->getGroup($document)) as $attribute) {
            if (!isset($this->eavAttributes[$attributeType][$attribute]['attribute_id'])) {
                if (isset($this->eavAttributes[$attributeType])) {
                    $message = sprintf('Attribute %s does not exist in the type %s', $attribute, $attributeType);
                } else {
                    $message = sprintf('Attribute type %s does not exist', $attributeType);
                }
                throw new \Migration\Exception($message);
            }
            $attributeId = $this->eavAttributes[$attributeType][$attribute]['attribute_id'];
            $attributeCode = $this->eavAttributes[$attributeType][$attribute]['attribute_code'];
            $this->skipAttributes[$attributeType][$attributeId] = $attributeCode;
        }
    }

    /**
     * Init eav entity collection
     *
     * @param string $name
     * @return void
     * @throws \Migration\Exception
     */
    public function initEavEntityCollection($name)
    {
        $this->entityName = $name;
        if (empty($this->documentAttributeTypes)) {
            $entities = array_keys($this->readerGroups->getGroup('eav_entities'));
            foreach ($entities as $entity) {
                $documents = $this->readerGroups->getGroup($entity);
                foreach ($documents as $item => $key) {
                    $this->documentAttributeTypes[$item] = $entity;
                    $this->initEavEntity($entity, $item, $key);
                }
            }
        }
    }

    /**
     * Init eav attributes
     *
     * @param string $attributeType
     * @return void
     */
    protected function initEavAttributes($attributeType)
    {
        if (isset($this->eavAttributes[$attributeType])) {
            return;
        }

        /** @var Mysql $adapter */
        $adapter = $this->destination->getAdapter();
        $query = $adapter->getSelect()
            ->from(
                ['et' => $this->destination->addDocumentPrefix('eav_entity_type')],
                []
            )->join(
                ['ea' => $this->destination->addDocumentPrefix('eav_attribute')],
                'et.entity_type_id = ea.entity_type_id',
                [
                    'attribute_id',
                    'backend_type',
                    'attribute_code',
                    'entity_type_id'
                ]
            )->where(
                'et.entity_type_code = ?',
                $attributeType
            );
        $attributes = $query->getAdapter()->fetchAll($query);

        foreach ($attributes as $attribute) {
            $this->eavAttributes[$attributeType][$attribute['attribute_code']] = $attribute;
            $this->eavAttributes[$attributeType][$attribute['attribute_id']] = $attribute;
        }
    }

    /**
     * Update eav attributes
     *
     * @throws \Zend_Db_Adapter_Exception
     * @return void
     */
    public function updateEavAttributes()
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

        $where = [];
        $entities = array_keys($this->readerGroups->getGroup('eav_entities'));
        foreach ($entities as $entity) {
            $documents = $this->readerGroups->getGroup($entity);
            $codes = [];
            foreach ($documents as $document => $key) {
                if ($key != 'entity_id') {
                    continue;
                }

                $codes = implode("','", array_keys($this->readerAttributes->getGroup($document)));
            }
            $where += [
                sprintf("attribute_code IN ('%s')", $codes),
                sprintf("entity_type_id = '%s'", $entityTypesByCode[$entity])
            ];
        }
        $adapter->getSelect()->getAdapter()->update(
            $this->destination->addDocumentPrefix('eav_attribute'),
            ['backend_type' => 'static'],
            $where
        );
    }
}
