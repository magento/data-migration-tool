<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\Rule;

use Migration\Handler\AbstractHandler;
use Migration\Reader\MapFactory;
use Migration\Reader\Map;
use Migration\ResourceModel\Destination;
use Migration\ResourceModel\Record;
use Migration\ResourceModel\Source;
use Migration\ResourceModel\RecordFactory;

/**
 * Class NormalizationIds
 */
class NormalizationIds extends AbstractHandler
{

    /**
     * @var Destination
     */
    protected $destination;

    /**
     * @var string
     */
    protected $normalizationDocument;

    /**
     * @var string
     */
    protected $normalizationField;

    /**
     * @param Destination $destination
     * @param null|string $normalizationDocument
     * @param null|string $normalizationField
     */
    public function __construct(
        Destination $destination,
        $normalizationDocument = null,
        $normalizationField = null
    ) {
        $this->normalizationDocument = $normalizationDocument;
        $this->normalizationField = $normalizationField;
        $this->destination = $destination;
    }

    /**
     * @param Record $recordToHandle
     * @param Record $oppositeRecord
     * @return void
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        $ids = explode(',', $recordToHandle->getValue($this->field));
        $normalizedRecords = [];
        foreach ($ids as $id) {
            $normalizedRecords[] = [
                'rule_id' => $recordToHandle->getValue('rule_id'),
                $this->normalizationField => $id
            ];
        }
        if ($normalizedRecords) {
            $this->destination->clearDocument($this->normalizationDocument);
            $this->destination->saveRecords($this->normalizationDocument, $normalizedRecords);
        }
    }
}
