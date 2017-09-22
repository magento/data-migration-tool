<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\Gallery;

use Migration\Handler\AbstractHandler;
use Migration\ResourceModel\Destination;
use Migration\ResourceModel\Record;
use Migration\Config;

/**
 * Class InsertValueToEntity
 */
class InsertValueToEntity extends AbstractHandler
{
    /**
     * @var Destination
     */
    protected $destination;

    /**
     * @var string
     */
    protected $valueToEntityDocument = 'catalog_product_entity_media_gallery_value_to_entity';

    /**
     * @var string
     */
    protected $entityField = 'entity_id';

    /**
     * @var string
     */
    protected $editionMigrate = '';

    /**
     * @param Destination $destination
     * @param Config $config
     */
    public function __construct(
        Destination $destination,
        Config $config
    ) {
        $this->destination = $destination;
        $this->editionMigrate = $config->getOption('edition_migrate');
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        $entityIdName = $this->editionMigrate == Config::EDITION_MIGRATE_OPENSOURCE_TO_OPENSOURCE
            ? $this->entityField
            : 'row_id';
        $record['value_id'] = $recordToHandle->getValue($this->field);
        $record[$entityIdName] = $recordToHandle->getValue($this->entityField);
        $this->destination->saveRecords($this->valueToEntityDocument, [$record]);
    }
}
