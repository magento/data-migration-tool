<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\Rule;

use Migration\Handler\AbstractHandler;
use Migration\ResourceModel\Destination;
use Migration\ResourceModel\Record;
use Migration\ResourceModel\RecordFactory;
use Migration\Config;

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
     * @var string
     */
    protected $editionMigrate = '';

    /**
     * @param Destination $destination
     * @param Config $config
     * @param null|string $normalizationDocument
     * @param null|string $normalizationField
     */
    public function __construct(
        Destination $destination,
        Config $config,
        $normalizationDocument = null,
        $normalizationField = null
    ) {
        $this->normalizationDocument = $normalizationDocument;
        $this->normalizationField = $normalizationField;
        $this->destination = $destination;
        $this->editionMigrate = $config->getOption('edition_migrate');
    }

    /**
     * @inheritdoc
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        $ids = explode(',', $recordToHandle->getValue($this->field));
        $normalizedRecords = [];
        $entityIdName = $this->editionMigrate == Config::EDITION_MIGRATE_OPENSOURCE_TO_OPENSOURCE
            ? 'rule_id'
            : 'row_id';
        foreach ($ids as $id) {
            $normalizedRecords[] = [
                $entityIdName => $recordToHandle->getValue('rule_id'),
                $this->normalizationField => $id
            ];
        }
        if ($normalizedRecords) {
            $this->destination->clearDocument($this->normalizationDocument);
            $this->destination->saveRecords($this->normalizationDocument, $normalizedRecords);
        }
    }
}
