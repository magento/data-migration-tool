<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Eav;

use Migration\App\Step\StageInterface;
use Migration\Logger\Logger;
use Migration\ProgressBar;
use Migration\Reader\GroupsFactory;

/**
 * Class Volume
 */
class Volume implements StageInterface
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
     * @var Logger
     */
    protected $logger;

    /**
     * @var ProgressBar
     */
    protected $progress;

    /**
     * @var \Migration\Reader\Groups
     */
    protected $groups;

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @param Helper $helper
     * @param InitialData $initialData
     * @param Logger $logger
     * @param ProgressBar $progress
     * @param GroupsFactory $groupsFactory
     */
    public function __construct(
        Helper $helper,
        InitialData $initialData,
        Logger $logger,
        ProgressBar $progress,
        GroupsFactory $groupsFactory
    ) {
        $this->initialData = $initialData;
        $this->helper = $helper;
        $this->logger = $logger;
        $this->progress = $progress;
        $this->groups = $groupsFactory->create('eav_document_groups_file');
    }

    /**
     * @return bool
     */
    public function perform()
    {
        $this->progress->start(count($this->groups->getGroup('documents')));
        $result = $this->validateAttributes() & $this->validateAttributeSetsAndGroups();
        $this->progress->finish();
        $this->printErrors();
        return (bool)$result;
    }

    /**
     * @return bool
     */
    public function validateAttributes()
    {
        $result = $this->validateEavAttributes();
        $result &= $this->validateCatalogEavAttributes();
        $result &= $this->validateCustomerEavAttributes();
        return (bool)$result;
    }

    /**
     * @return bool
     */
    protected function validateEavAttributes()
    {
        $result = true;
        $sourceAttrbutes = $this->initialData->getAttributes('source');
        foreach ($this->helper->getDestinationRecords('eav_attribute') as $attribute) {
            if (isset($sourceAttrbutes[$attribute['attribute_id']])
                && $sourceAttrbutes[$attribute['attribute_id']]['attribute_code'] != $attribute['attribute_code']
            ) {
                $result = false;
                $this->errors[] = 'Source and Destination attributes mismatch. Attribute id: '
                    . $attribute['attribute_id'];
            }

            foreach (['attribute_model', 'backend_model', 'frontend_model', 'source_model'] as $field) {
                if ($attribute[$field] !== null && !class_exists($attribute[$field])) {
                    $result = false;
                    $this->errors[] = 'Incorrect value in: eav_attribute.' . $field .' for attribute_code='
                        . $attribute['attribute_code'];
                }
            }
        }

        return $result;
    }

    /**
     * @return bool
     */
    protected function validateCustomerEavAttributes()
    {
        $result = true;
        foreach ($this->helper->getDestinationRecords('customer_eav_attribute') as $attribute) {
            foreach (['data_model'] as $field) {
                if ($attribute[$field] !== null && !class_exists($attribute[$field])) {
                    $result = false;
                    $this->errors[] = 'Incorrect value: customer_eav_attribute.' . $field
                        . ' for attribute_id=' . $attribute['attribute_id'];
                }
            }
        }
        return $result;
    }

    /**
     * @return bool
     */
    protected function validateCatalogEavAttributes()
    {
        $result = true;
        foreach ($this->helper->getDestinationRecords('catalog_eav_attribute') as $attribute) {
            foreach (['frontend_input_renderer'] as $field) {
                if ($attribute[$field] !== null && !class_exists($attribute[$field])) {
                    $result = false;
                    $this->errors[] = 'Incorrect value in: catalog_eav_attribute.' . $field
                        . ' for attribute_id=' . $attribute['attribute_id'];
                }
            }
        }
        return $result;
    }

    /**
     * @return bool
     */
    public function validateAttributeSetsAndGroups()
    {
        $result = true;
        $sourceRecords = $this->helper->getSourceRecordsCount('eav_attribute_set');
        $initialDestRecords = count($this->initialData->getAttributeSets('dest'));
        if ($this->helper->getDestinationRecordsCount('eav_attribute_set') != $sourceRecords + $initialDestRecords) {
            $result = false;
            $this->errors[] = 'Incorrect number of entities in document: eav_attribute_set';
        }

        $sourceRecords = $this->helper->getSourceRecordsCount('eav_attribute_group');
        $initialDestRecords = count($this->initialData->getAttributeGroups('dest'));
        if ($this->helper->getDestinationRecordsCount('eav_attribute_group') != $sourceRecords + $initialDestRecords) {
            $result = false;
            $this->errors[] = 'Incorrect number of entities in document: eav_attribute_group';
        }

        return $result;
    }

    /**
     * @param mixed $expected
     * @param mixed $actual
     * @param string $message
     * @return bool
     */
    public function assertEqual($expected, $actual, $message)
    {
        $result = true;
        if ($expected != $actual) {
            $result = false;
            $this->errors[] = $message;
        }

        return $result;
    }

    /**
     * @param string $message
     * @return void
     */
    protected function logError($message)
    {
        $this->logger->log(Logger::ERROR, $message);
    }

    /**
     * Print Volume check errors
     * @return void
     */
    protected function printErrors()
    {
        foreach ($this->errors as $error) {
            $this->logError(PHP_EOL . $error);
        }
    }
}
