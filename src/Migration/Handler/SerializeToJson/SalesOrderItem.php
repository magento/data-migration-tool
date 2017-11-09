<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\SerializeToJson;

use Migration\ResourceModel\Record;
use Migration\Handler\AbstractHandler;
use Migration\Logger\Logger;
use Migration\Model\DocumentIdField;

/**
 * Handler to transform field from sales_order_item
 * @SuppressWarnings(CyclomaticComplexity)
 */
class SalesOrderItem extends AbstractHandler
{
    /**
     * Sometimes fields has a broken serialize data
     * If property sets to true, ignore all notices from unserialize()
     *
     * @var bool
     *
     */
    private $ignoreBrokenData;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var DocumentIdField
     */
    private $documentIdFiled;

    /**
     * @param Logger $logger
     * @param DocumentIdField $documentIdField
     * @param bool $ignoreBrokenData
     */
    public function __construct(Logger $logger, DocumentIdField $documentIdField, $ignoreBrokenData = true)
    {
        $this->logger = $logger;
        $this->ignoreBrokenData = (bool)$ignoreBrokenData;
        $this->documentIdFiled = $documentIdField;
    }

    /**
     * @param Record $recordToHandle
     * @param Record $oppositeRecord
     * @return void
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        $value = $recordToHandle->getValue($this->field);
        if (null !== $value) {
            $unserializeData = $this->ignoreBrokenData ? @unserialize($value) : unserialize($value);
            if (isset($unserializeData['options'])) {
                foreach ($unserializeData['options'] as $key => $option) {
                    if (array_key_exists('option_type', $option) && $option['option_type'] === 'file') {
                        $optionValue = $option['option_value'] ? unserialize($option['option_value']) :
                            $option['option_value'];
                        $unserializeData['options'][$key]['option_value'] = json_encode($optionValue);
                    }
                }
            }
            if (isset($unserializeData['bundle_selection_attributes'])) {
                $bundleSelectionAttributes = $unserializeData['bundle_selection_attributes'] ?
                    unserialize($unserializeData['bundle_selection_attributes']) :
                    $unserializeData['bundle_selection_attributes'];
                $unserializeData['bundle_selection_attributes'] = json_encode($bundleSelectionAttributes);
            }
            if (false === $unserializeData) {
                $this->logger->warning(sprintf(
                    'Could not unserialize data of %s.%s with record id %s',
                    $recordToHandle->getDocument()->getName(),
                    $this->field,
                    $recordToHandle->getValue($this->documentIdFiled->getFiled($recordToHandle->getDocument()))
                ));
                $this->logger->warning("\n");
            } else {
                $value = json_encode($unserializeData);
            }
        }
        $recordToHandle->setValue($this->field, $value);
    }
}
