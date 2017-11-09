<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

use Migration\ResourceModel\Record;
use Migration\Logger\Logger;
use Migration\Model\DocumentIdField;

/**
 * Handler to transform field according to the map
 */
class SerializeToJson extends AbstractHandler
{
    /**
     * Sometimes fields has a broken serialize data, for example enterprise_logging_event_changes.result_data.
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

    public function __construct(Logger $logger, DocumentIdField $documentIdField, $ignoreBrokenData = true)
    {
        $this->logger = $logger;
        $this->documentIdFiled = $documentIdField;
        $this->ignoreBrokenData = (bool)$ignoreBrokenData;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        $value = $recordToHandle->getValue($this->field);
        if (null !== $value) {
            $unserializeData = $this->ignoreBrokenData ? @unserialize($value) : unserialize($value);
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
