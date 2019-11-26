<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * Sometimes fields has a broken serialized data
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
     * @inheritdoc
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        $value = $recordToHandle->getValue($this->field);
        if (null !== $value) {
            try {
                $unserializedData = unserialize($value);
            } catch (\Exception $exception) {
                if (!$this->ignoreBrokenData) {
                    throw new \Exception($exception);
                }
                $this->logger->warning(sprintf(
                    'Could not unserialize data of %s.%s with record id %s',
                    $recordToHandle->getDocument()->getName(),
                    $this->field,
                    $recordToHandle->getValue($this->documentIdFiled->getFiled($recordToHandle->getDocument()))
                ));
                $this->logger->warning("\n");
                $recordToHandle->setValue($this->field, null);
                return;
            }

            if (isset($unserializedData['options'])) {
                foreach ($unserializedData['options'] as $key => $option) {
                    if (is_array($option)
                        && array_key_exists('option_type', $option)
                        && $option['option_type'] === 'file'
                    ) {
                        $optionValue = $option['option_value'] ? unserialize($option['option_value']) :
                            $option['option_value'];
                        $unserializedData['options'][$key]['option_value'] = json_encode($optionValue);
                    }
                }
            }
            if (isset($unserializedData['bundle_selection_attributes'])) {
                $bundleSelectionAttributes = $unserializedData['bundle_selection_attributes'] ?
                    unserialize($unserializedData['bundle_selection_attributes']) :
                    $unserializedData['bundle_selection_attributes'];
                $unserializedData['bundle_selection_attributes'] = json_encode($bundleSelectionAttributes);
            }
            $value = json_encode($unserializedData);
        }
        $recordToHandle->setValue($this->field, $value);
    }
}
