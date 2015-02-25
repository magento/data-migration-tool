<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Volume;

use Migration\Logger\Logger;
use Migration\ProgressBar;
use Migration\Step\Eav\Helper;
use Migration\Step\Eav\InitialData;

/**
 * Class Eav
 */
class Eav
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
     * @param Helper $helper
     * @param InitialData $initialData
     */
    public function __construct(Helper $helper, InitialData $initialData, Logger $logger, ProgressBar $progress)
    {
        $this->initialData = $initialData;
        $this->helper = $helper;
        $this->logger = $logger;
        $this->progress = $progress;
    }

    /**
     * @return bool
     */
    public function perform()
    {
        $this->progress->start(count($this->helper->getDocumentsMap()));
        $result = $this->validateAttributes();
        $result = $result & $this->validateAttributeSetsAndGroups();
        $result = $result & $this->validateJustCopyTables();
        $this->progress->finish();
        return (bool)$result;
    }

    /**
     * @return bool
     */
    public function validateAttributes()
    {
        $result = true;
        foreach ($this->helper->getDestinationRecords('eav_attribute') as $attribute) {
            if (isset($this->initialData->getAttributes('source')[$attribute['attribute_id']])
                && $this->initialData->getAttributes('source')[$attribute['attribute_id']]['attribute_code'] != $attribute['attribute_code']
            ) {
                $result = false;
                $this->logError('Source and Destination attributes mismatch. Attribute id: ' . $attribute['attribute_id']);
            }

            foreach (['attribute_model', 'backend_model', 'frontend_model', 'source_model'] as $field) {
                if (!is_null($attribute[$field]) && !class_exists($attribute[$field])) {
                    $this->logError(
                        "Incorrect value in: eav_attribute.$field for attribute_code={$attribute['attribute_code']}"
                    );
                }
            }
        }

        foreach ($this->helper->getDestinationRecords('customer_eav_attribute') as $attribute) {
            foreach (['data_model'] as $field) {
                if (!is_null($attribute[$field]) && !class_exists($attribute[$field])) {
                    $result = false;
                    $this->logError(
                        "Incorrect value in: customer_eav_attribute.$field for attribute_id={$attribute['attribute_id']}"
                    );
                }
            }
        }

        foreach ($this->helper->getDestinationRecords('catalog_eav_attribute') as $attribute) {
            foreach (['frontend_input_renderer'] as $field) {
                if (!is_null($attribute[$field]) && !class_exists($attribute[$field])) {
                    $result = false;
                    $this->logError(
                        "Incorrect value in: catalog_eav_attribute.$field for attribute_id={$attribute['attribute_id']}"
                    );
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
            $this->logError('Incorrect number of entities in document: eav_attribute_set');
        }

        $sourceRecords = $this->helper->getSourceRecordsCount('eav_attribute_group');
        $initialDestRecords = count($this->initialData->getAttributeGroups('dest'));
        if ($this->helper->getDestinationRecordsCount('eav_attribute_group') != $sourceRecords + $initialDestRecords) {
            $result = false;
            $this->logError('Incorrect number of entities in document: eav_attribute_group');
        }

        return $result;
    }

    /**
     * @return bool|int
     */
    public function validateJustCopyTables()
    {
        $result = true;
        foreach ($this->helper->getJustCopyDocuments() as $document) {
            $result = $result & $this->assertEqual(
                $this->helper->getSourceRecordsCount($document),
                $this->helper->getDestinationRecordsCount($document),
                'Incorrect number of entities in document: ' . $document
            );
        }

        return $result;
    }

    /**
     * @param $expected
     * @param $actual
     * @param $message
     * @return bool
     */
    public function assertEqual($expected, $actual, $message)
    {
        $result = true;
        if ($expected != $actual) {
            $result = false;
            $this->logError($message);
        }

        return $result;
    }

    /**
     * @param $message
     */
    protected function logError($message)
    {
        $this->logger->log(Logger::ERROR, $message);
    }
}
