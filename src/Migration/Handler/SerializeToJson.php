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
     * @var bool
     *
     */
    private $migrateBrokenData;

    /**
     * @var bool
     */
    private $suppressWarning;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var DocumentIdField
     */
    private $documentIdFiled;

    public function __construct(
        Logger $logger,
        DocumentIdField $documentIdField,
        $migrateBrokenData = true,
        $suppressWarning = false
    ) {
        $this->logger = $logger;
        $this->documentIdFiled = $documentIdField;
        if ($migrateBrokenData === true || $migrateBrokenData == 'true') {
            $this->migrateBrokenData = true;
        } else {
            $this->migrateBrokenData = false;
        }
        if ($suppressWarning === true || $suppressWarning == 'true') {
            $this->suppressWarning = true;
        } else {
            $this->suppressWarning = false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        $value = $recordToHandle->getValue($this->field);
        if (null !== $value) {
            $unserializeData = $this->migrateBrokenData ? @unserialize($value) : unserialize($value);
            if (false === $unserializeData && !$this->suppressWarning) {
                $this->logger->warning(sprintf(
                    'Could not unserialize data of %s.%s with record id %s',
                    $recordToHandle->getDocument()->getName(),
                    $this->field,
                    $recordToHandle->getValue($this->documentIdFiled->getFiled($recordToHandle->getDocument()))
                ));
                $this->logger->warning("\n");
            }
            if (false !== $unserializeData) {
                $value = json_encode($unserializeData);
            }
        }
        $recordToHandle->setValue($this->field, $value);
    }
}
