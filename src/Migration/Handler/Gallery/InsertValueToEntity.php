<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\Gallery;

use Migration\Handler\AbstractHandler;
use Migration\ResourceModel\Destination;
use Migration\ResourceModel\Record;

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
     * @param Destination $destination
     */
    public function __construct(
        Destination $destination
    ) {
        $this->destination = $destination;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        $record['value_id'] = $recordToHandle->getValue($this->field);
        $record['entity_id'] = $recordToHandle->getValue($this->entityField);
        $this->destination->saveRecords($this->valueToEntityDocument, [$record]);
    }
}
