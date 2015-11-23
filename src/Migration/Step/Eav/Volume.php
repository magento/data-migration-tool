<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Eav;

use Migration\App\Step\AbstractVolume;
use Migration\Logger\Logger;
use Migration\App\ProgressBar;
use Migration\Reader\GroupsFactory;

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
     * @param Helper $helper
     * @param InitialData $initialData
     * @param Logger $logger
     * @param ProgressBar\LogLevelProcessor $progress
     * @param GroupsFactory $groupsFactory
     */
    public function __construct(
        Helper $helper,
        InitialData $initialData,
        Logger $logger,
        ProgressBar\LogLevelProcessor $progress,
        GroupsFactory $groupsFactory
    ) {
        $this->initialData = $initialData;
        $this->helper = $helper;
        $this->progress = $progress;
        $this->groups = $groupsFactory->create('eav_document_groups_file');
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
        return $this->checkForErrors(Logger::ERROR);
    }

    /**
     * @return void
     */
    protected function validateAttributes()
    {
        $this->validateEavAttributes();
        $this->validateCatalogEavAttributes();
        $this->validateCustomerEavAttributes();
    }

    /**
     * @return void
     */
    protected function validateEavAttributes()
    {
        $sourceAttrbutes = $this->initialData->getAttributes('source');
        foreach ($this->helper->getDestinationRecords('eav_attribute') as $attribute) {
            if (isset($sourceAttrbutes[$attribute['attribute_id']])
                && $sourceAttrbutes[$attribute['attribute_id']]['attribute_code'] != $attribute['attribute_code']
            ) {
                $this->errors[] = 'Source and Destination attributes mismatch. Attribute id: '
                    . $attribute['attribute_id'];
            }

            foreach (['attribute_model', 'backend_model', 'frontend_model', 'source_model'] as $field) {
                if ($attribute[$field] !== null && !class_exists($attribute[$field])) {
                    $this->errors[] = 'Incorrect value: '. $attribute[$field]
                        .' in: eav_attribute.' . $field
                        .' for attribute_code=' . $attribute['attribute_code'];
                }
            }
        }
    }

    /**
     * @return void
     */
    protected function validateCustomerEavAttributes()
    {
        foreach ($this->helper->getDestinationRecords('customer_eav_attribute') as $attribute) {
            foreach (['data_model'] as $field) {
                if ($attribute[$field] !== null && !class_exists($attribute[$field])) {
                    $this->errors[] = 'Incorrect value: customer_eav_attribute.' . $field
                        . ' for attribute_id=' . $attribute['attribute_id'];
                }
            }
        }
    }

    /**
     * @return void
     */
    protected function validateCatalogEavAttributes()
    {
        foreach ($this->helper->getDestinationRecords('catalog_eav_attribute') as $attribute) {
            foreach (['frontend_input_renderer'] as $field) {
                if ($attribute[$field] !== null && !class_exists($attribute[$field])) {
                    $this->errors[] = 'Incorrect value: '. $attribute[$field]
                        . ' in: catalog_eav_attribute.' . $field
                        . ' for attribute_id=' . $attribute['attribute_id'];
                }
            }
        }
    }

    /**
     * @return void
     */
    public function validateAttributeSetsAndGroups()
    {
        $sourceRecords = $this->helper->getSourceRecordsCount('eav_attribute_set');
        $initialDestRecords = count($this->initialData->getAttributeSets('dest'));
        if ($this->helper->getDestinationRecordsCount('eav_attribute_set') != $sourceRecords + $initialDestRecords) {
            $this->errors[] = 'Mismatch of entities in the document: eav_attribute_set';
        }

        $sourceRecords = $this->helper->getSourceRecordsCount('eav_attribute_group');
        $initialDestRecords = count($this->initialData->getAttributeGroups('dest'));
        if ($this->helper->getDestinationRecordsCount('eav_attribute_group') != $sourceRecords + $initialDestRecords) {
            $this->errors[] = 'Mismatch of entities in the document: eav_attribute_group';
        }
    }
}
