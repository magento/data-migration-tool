<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Eav;

use Migration\MapReader;
use Migration\RecordTransformer;
use Migration\RecordTransformerFactory;
use Migration\Resource\Destination;
use Migration\Resource\Document;
use Migration\Resource\Source;

/**
 * Class Helper
 */
class Helper
{
    /**
     * @var MapReader
     */
    protected $map;

    /**
     * @var Destination
     */
    protected $destination;

    /**
     * @var RecordTransformerFactory
     */
    protected $factory;

    /**
     * @param MapReader $mapReader
     * @param Source $source
     * @param Destination $destination
     * @param RecordTransformerFactory $factory
     */
    public function __construct(
        MapReader $mapReader,
        Source $source,
        Destination $destination,
        RecordTransformerFactory $factory
    ) {
        $this->map = $mapReader;
        $this->source = $source;
        $this->destination = $destination;
        $this->factory = $factory;
    }

    /**
     * @param $sourceDocumentName
     * @return int
     */
    public function getSourceRecordsCount($sourceDocumentName)
    {
        return $this->source->getRecordsCount($sourceDocumentName);
    }

    /**
     * @param $sourceDocumentName
     * @return int
     */
    public function getDestinationRecordsCount($sourceDocumentName)
    {
        return $this->destination->getRecordsCount(
            $this->map->getDocumentMap($sourceDocumentName, MapReader::TYPE_SOURCE)
        );
    }

    /**
     * @param string $sourceDocName
     * @param array $keyFields
     * @return array
     */
    public function getDestinationRecords($sourceDocName, $keyFields = [])
    {
        $destinationDocumentName = $this->map->getDocumentMap($sourceDocName, MapReader::TYPE_SOURCE);
        $data = [];
        $count = $this->destination->getRecordsCount($destinationDocumentName);
        foreach ($this->destination->getRecords($destinationDocumentName, 0, $count) as $row) {
            if ($keyFields) {
                $key = [];
                foreach ($keyFields as $keyField) {
                    $key[] = $row[$keyField];
                }
                $data[implode('-', $key)] = $row;
            } else {
                $data[] = $row;
            }
        }

        return $data;
    }

    /**
     * @param string $sourceDocName
     * @param array $keyFields
     * @return array
     */
    public function getSourceRecords($sourceDocName, $keyFields = [])
    {
        $data = [];
        $count = $this->source->getRecordsCount($sourceDocName);
        foreach ($this->source->getRecords($sourceDocName, 0, $count) as $row) {
            if ($keyFields) {
                $key = [];
                foreach ($keyFields as $keyField) {
                    $key[] = $row[$keyField];
                }
                $data[implode('-', $key)] = $row;
            } else {
                $data[] = $row;
            }
        }

        return $data;
    }

    /**
     * @param Document $sourceDocument
     * @param Document $destinationDocument
     * @return RecordTransformer
     */
    public function getRecordTransformer($sourceDocument, $destinationDocument)
    {
        return $this->factory->create([
            'sourceDocument' => $sourceDocument,
            'destDocument' => $destinationDocument,
            'mapReader' => $this->map
        ])->init();
    }

    /**
     * EAV tables mapping
     *
     * @return array
     */
    public function getDocumentsMap()
    {
        return [
            'eav_attribute_group' => 'eav_attribute_group',
            'eav_attribute_set' => 'eav_attribute_set',
            'eav_attribute' => 'eav_attribute',
            'eav_entity_attribute' => 'eav_entity_attribute',
            'catalog_eav_attribute' => 'catalog_eav_attribute',
            'customer_eav_attribute' => 'customer_eav_attribute',
            'eav_entity_type' => 'eav_entity_type',
            'customer_eav_attribute_website' => 'customer_eav_attribute_website',
            'eav_attribute_label' => 'eav_attribute_label',
            'eav_attribute_option' => 'eav_attribute_option',
            'eav_attribute_option_value' => 'eav_attribute_option_value',
            'eav_entity' => 'eav_entity',
            'eav_entity_datetime' => 'eav_entity_datetime',
            'eav_entity_decimal' => 'eav_entity_decimal',
            'eav_entity_int' => 'eav_entity_int',
            'eav_entity_store' => 'eav_entity_store',
            'eav_entity_text' => 'eav_entity_text',
            'eav_entity_varchar' => 'eav_entity_varchar',
            'eav_form_element' => 'eav_form_element',
            'eav_form_fieldset' => 'eav_form_fieldset',
            'eav_form_fieldset_label' => 'eav_form_fieldset_label',
            'eav_form_type' => 'eav_form_type',
            'eav_form_type_entity' => 'eav_form_type_entity',
            'enterprise_rma_item_eav_attribute' => 'magento_rma_item_eav_attribute',
            'enterprise_rma_item_eav_attribute_website' => 'magento_rma_item_eav_attribute_website'
        ];
    }

    /**
     * List of tables to be copied without data merge
     *
     * @return array
     */
    public function getJustCopyDocuments()
    {
        return [
            'customer_eav_attribute_website',
            'eav_attribute_label',
            'eav_attribute_option',
            'eav_attribute_option_value',
            'eav_entity',
            'eav_entity_datetime',
            'eav_entity_decimal',
            'eav_entity_int',
            'eav_entity_store',
            'eav_entity_text',
            'eav_entity_varchar',
            'eav_form_element',
            'eav_form_fieldset',
            'eav_form_fieldset_label',
            'eav_form_type',
            'eav_form_type_entity',
            'enterprise_rma_item_eav_attribute_website'
        ];
    }
}
