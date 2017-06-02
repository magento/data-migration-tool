<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Eav;

use Migration\App\Step\AbstractVolume;
use Migration\Logger\Logger;
use Migration\App\ProgressBar;
use Migration\Reader\GroupsFactory;
use Migration\ResourceModel\Destination;
use Migration\Step\Eav\Model\IgnoredAttributes;

/**
 * Class Volume
 */
class Volume extends AbstractVolume
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var InitialData
     */
    protected $initialData;

    /**
     * @var ProgressBar\LogLevelProcessor
     */
    protected $progress;

    /**
     * @var \Migration\Reader\Groups
     */
    protected $groups;

    /**
     * @var Destination
     */
    protected $destination;

    /**
     * @var array
     */
    protected $tableKeys;

    /**
     * @var IgnoredAttributes
     */
    protected $ignoredAttributes;

    /**
     * Eav Attributes that can be validated
     * @var array
     */
    private $eavAttributesForValidation = [
        'eav_attribute' => [
            'attribute_model',
            'backend_model',
            'frontend_model',
            'source_model',
        ],
        'catalog_eav_attribute' => [
            'frontend_input_renderer',
        ],
        'customer_eav_attribute' => [
            'data_model',
        ],
        'eav_entity_type' => [
            'entity_model',
            'attribute_model',
            'increment_model',
            'entity_attribute_collection',
        ]
    ];

    /**
     * @param Helper $helper
     * @param InitialData $initialData
     * @param IgnoredAttributes $ignoredAttributes
     * @param Logger $logger
     * @param ProgressBar\LogLevelProcessor $progress
     * @param GroupsFactory $groupsFactory
     * @param Destination $destination
     */
    public function __construct(
        Helper $helper,
        InitialData $initialData,
        IgnoredAttributes $ignoredAttributes,
        Logger $logger,
        ProgressBar\LogLevelProcessor $progress,
        GroupsFactory $groupsFactory,
        Destination $destination
    ) {
        $this->initialData = $initialData;
        $this->ignoredAttributes = $ignoredAttributes;
        $this->helper = $helper;
        $this->progress = $progress;
        $this->groups = $groupsFactory->create('eav_document_groups_file');
        $this->destination = $destination;
        parent::__construct($logger);
    }

    /**
     * @return bool
     */
    public function perform()
    {
        $this->progress->start(count($this->groups->getGroup('documents')));
        $this->validateAttributes();
        $this->validateAttributeSetsAndGroups();
        $this->progress->finish();
        $result = $this->checkForErrors(Logger::ERROR);
        if ($result) {
            $this->helper->deleteBackups();
        }
        return $result;
    }

    /**
     * @return void
     */
    protected function validateAttributes()
    {
        $this->validateDestinationEavTable('eav_attribute', ['checkAttributesMismatch']);
        $this->validateDestinationEavTable('catalog_eav_attribute');
        $this->validateDestinationEavTable('customer_eav_attribute');
        $this->validateDestinationEavTable('eav_entity_type');
    }

    /**
     * @param string $tableName
     * @param array  $conditions
     * @return void
     */
    protected function validateDestinationEavTable($tableName, array $conditions = [])
    {
        if (!isset($this->eavAttributesForValidation[$tableName])) {
            $this->errors[] = 'Table ' . $tableName . ' can not be validated. Fields must be set.';
            return;
        }

        $tableFields = $this->eavAttributesForValidation[$tableName];
        $destinationRecords = $this->helper->getDestinationRecords($tableName);

        $primaryKeyName = $this->getPrimaryKeyName($tableName);

        foreach ($destinationRecords as $attribute) {
            foreach ($tableFields as $field) {
                if (!empty($attribute[$field]) && !class_exists($attribute[$field])) {
                    $this->errors[] = sprintf(
                        'Class %s does not exist but mentioned in: %s.%s for %s=%s',
                        $attribute[$field],
                        $tableName,
                        $field,
                        $primaryKeyName,
                        $attribute[$primaryKeyName]
                    );
                }
            }
            if (!empty($conditions)) {
                $this->validateCustomConditions($attribute, $conditions);
            }
        }
    }

    /**
     * @param array $attribute
     * @param array $conditions
     * @return void
     */
    protected function validateCustomConditions(array $attribute, array $conditions)
    {
        foreach ($conditions as $condition) {
            if (method_exists($this, $condition)) {
                $this->$condition($attribute);
            }
        }
    }

    /**
     * @param array $attribute
     * @return void
     */
    protected function checkAttributesMismatch($attribute)
    {
        $sourceAttributes = $this->ignoredAttributes
            ->clearIgnoredAttributes($this->initialData->getAttributes('source'));

        if (isset($sourceAttributes[$attribute['attribute_id']])
            && ($sourceAttributes[$attribute['attribute_id']]['attribute_code'] != $attribute['attribute_code'])
        ) {
            $this->errors[] = sprintf(
                'Source and Destination attributes mismatch. Attribute id:%s',
                $attribute['attribute_id']
            );
        }
    }

    /**
     * @return void
     */
    protected function validateAttributeSetsAndGroups()
    {
        $sourceRecords = $this->helper->getSourceRecordsCount('eav_attribute_set');
        $initialDestRecords = count($this->initialData->getAttributeSets('dest'));
        if ($this->helper->getDestinationRecordsCount('eav_attribute_set') != $sourceRecords + $initialDestRecords) {
            $this->errors[] = 'Mismatch of entities in the document: eav_attribute_set';
        }

        $sourceRecords = $this->helper->getSourceRecordsCount('eav_attribute_group');
        $addedRecords = count($this->helper->getAddedGroups());
        $initialDestRecords = count($this->initialData->getAttributeGroups('dest'));
        if ($this->helper->getDestinationRecordsCount('eav_attribute_group') !=
            $sourceRecords + $addedRecords + $initialDestRecords
        ) {
            $this->errors[] = 'Mismatch of entities in the document: eav_attribute_group';
        }
    }

    /**
     * @param string $documentName
     * @return mixed
     */
    protected function getPrimaryKeyName($documentName)
    {
        if (isset($this->tableKeys[$documentName])) {
            return $this->tableKeys[$documentName];
        }

        $this->tableKeys[$documentName] = null;
        $destinationFields = $this->destination->getDocument($documentName)->getStructure()->getFields();
        foreach ($destinationFields as $params) {
            if ($params['PRIMARY']) {
                $this->tableKeys[$documentName] = $params['COLUMN_NAME'];
                break;
            }
        }

        return $this->tableKeys[$documentName];
    }
}
