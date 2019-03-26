<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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

    /**
     * @param Logger $logger
     * @param DocumentIdField $documentIdField
     * @param bool $migrateBrokenData
     * @param bool $suppressWarning
     */
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
     * @inheritdoc
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        $value = $recordToHandle->getValue($this->field);
        if (null !== $value) {
            try {
                $unserializeData = unserialize($value);
            } catch (\Exception $exception) {
                if (!$this->migrateBrokenData) {
                    throw new \Exception($exception);
                }
                if (!$this->suppressWarning) {
                    $this->logger->warning(sprintf(
                        'Could not unserialize data of %s.%s with record id %s',
                        $recordToHandle->getDocument()->getName(),
                        $this->field,
                        $recordToHandle->getValue($this->documentIdFiled->getFiled($recordToHandle->getDocument()))
                    ));
                    $this->logger->warning("\n");
                }
                $recordToHandle->setValue($this->field, null);
                return;
            }
            $value = json_encode($unserializeData);
        }
        $recordToHandle->setValue($this->field, $value);
    }
}
