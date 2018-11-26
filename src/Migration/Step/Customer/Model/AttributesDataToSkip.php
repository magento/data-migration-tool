<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Customer\Model;

use Migration\Reader\GroupsFactory;

/**
 * The class is responsible for retrieving attribute codes and ids of customers
 * which data should not be migrated to data tables
 * (i.e. customer_entity_int, customer_entity_varchar, customer_address_entity_text ...)
 */
class AttributesDataToSkip
{
    /**
     * @var \Migration\Reader\Groups
     */
    private $readerGroups;

    /**
     * @var \Migration\Reader\Groups
     */
    private $readerAttributes;

    /**
     * @var array
     */
    private $skipAttributes;

    /**
     * @var EntityTypeCode
     */
    private $entityTypeCode;

    /**
     * @param GroupsFactory $groupsFactory
     * @param EntityTypeCode $entityTypeCode
     */
    public function __construct(
        GroupsFactory $groupsFactory,
        EntityTypeCode $entityTypeCode
    ) {
        $this->readerAttributes = $groupsFactory->create('customer_attribute_groups_file');
        $this->readerGroups = $groupsFactory->create('customer_document_groups_file');
        $this->entityTypeCode = $entityTypeCode;
    }

    /**
     * Retrieves array with attributes which data should be skipped
     * during migration of data tables
     * (i.e. customer_entity_int, customer_entity_varchar, customer_address_entity_text ...)
     *
     * Sample of returning array:
     * <code>
     * [
     *      'customer' => [
     *          3 => 'created_in',
     *          4 => 'prefix',
     *          5 => 'firstname'
     *      ],
     *      'customer_address' => [
     *          26 => 'city',
     *          24 => 'company',
     *          27 => 'country_id'
     *      ]
     * ]
     * </code>
     *
     * @param mixed $sourceDocumentName
     * @return array
     * @throws \Migration\Exception
     */
    public function getSkippedAttributes($sourceDocumentName = null)
    {
        $skipAttributes = [];
        $this->fetchSkippedAttributes();
        if ($sourceDocumentName !== null) {
            $entityTypeCode = $this->entityTypeCode->getEntityTypeCodeByDocumentName($sourceDocumentName);
            return isset($this->skipAttributes[$entityTypeCode]) ? $this->skipAttributes[$entityTypeCode] : null;
        } else {
            foreach ($this->skipAttributes as $attributes) {
                $skipAttributes += $attributes;
            }
        }
        return $skipAttributes;
    }

    /**
     * Fetch skipped attributes and store in class property variable
     *
     * @throws \Migration\Exception
     */
    private function fetchSkippedAttributes()
    {
        if ($this->skipAttributes !== null) {
            return;
        }
        $this->skipAttributes = [];
        $entityDocuments = array_keys($this->readerGroups->getGroup('source_entity_documents'));
        foreach ($entityDocuments as $entityDocument) {
            $entityTypeCode = $this->entityTypeCode->getEntityTypeCodeByDocumentName($entityDocument);
            $eavAttributes = $this->entityTypeCode->getAttributesData($entityTypeCode);
            $attributeCodes = array_keys($this->readerAttributes->getGroup($entityDocument));
            foreach ($attributeCodes as $attributeCode) {
                if (!isset($eavAttributes[$attributeCode])) {
                    throw new \Migration\Exception(
                        sprintf(
                            'Attribute %s does not exist in the type %s',
                            $attributeCode,
                            $entityTypeCode
                        )
                    );
                }
                $attributeId = $eavAttributes[$attributeCode]['attribute_id'];
                $this->skipAttributes[$entityTypeCode][$attributeId] = $attributeCode;
            }
        }
    }
}
