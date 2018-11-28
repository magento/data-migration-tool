<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\Gallery;

use Migration\Handler\AbstractHandler;
use Migration\ResourceModel\Record;
use Migration\ResourceModel\Adapter\Mysql;
use Migration\ResourceModel\Source;

/**
 * Class SetEntityId
 */
class SetEntityId extends AbstractHandler
{
    /**
     * @var string
     */
    protected $mediaGalleryDocument = 'catalog_product_entity_media_gallery';

    /**
     * @var string
     */
    protected $valueIdField = 'value_id';

    /**
     * @var string
     */
    protected $entityIdField = 'entity_id';

    /**
     * @var Source
     */
    protected $source;

    /**
     * @param Source $source
     */
    public function __construct(Source $source)
    {
        $this->source = $source;
    }

    /**
     * @inheritdoc
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        $entityId = $this->getEntityId($recordToHandle->getValue($this->valueIdField));
        $recordToHandle->setValue($this->field, $entityId);
    }

    /**
     * Get entity id
     *
     * @param int $valueId
     * @return int
     */
    protected function getEntityId($valueId)
    {
        /** @var Mysql $adapter */
        $adapter = $this->source->getAdapter();
        $query = $adapter->getSelect()
            ->from(
                ['mg' => $this->source->addDocumentPrefix($this->mediaGalleryDocument)],
                [$this->entityIdField]
            )->where("mg.{$this->valueIdField} = ?", $valueId);
        return (int) $query->getAdapter()->fetchOne($query);
    }
}
