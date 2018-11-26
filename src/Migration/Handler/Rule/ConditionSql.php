<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\Rule;

use Migration\Handler\AbstractHandler;
use Migration\Reader\MapFactory;
use Migration\Reader\Map;
use Migration\Reader\MapInterface;
use Migration\ResourceModel\Destination;
use Migration\ResourceModel\Record;
use Migration\ResourceModel\Source;

/**
 * Class ConditionSql
 */
class ConditionSql extends AbstractHandler
{
    /**
     * @var Map
     */
    protected $map;

    /**
     * @var Source
     */
    protected $source;

    /**
     * @var Destination
     */
    protected $destination;

    /**
     * @param MapFactory $mapFactory
     * @param Source $source
     * @param Destination $destination
     */
    public function __construct(MapFactory $mapFactory, Source $source, Destination $destination)
    {
        $this->map = $mapFactory->create('map_file');
        $this->source = $source;
        $this->destination = $destination;
    }

    /**
     * @inheritdoc
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        $sourcePatterns = [];
        $destinationPatters = [];
        foreach ($this->source->getDocumentList() as $document) {
            $destDocumentName = $this->map->getDocumentMap($document, MapInterface::TYPE_SOURCE);
            if ($destDocumentName === false) {
                continue;
            }
            $sourcePatterns[] = sprintf('`%s`', $this->source->addDocumentPrefix($document));
            $destinationPatters[] = sprintf('`%s`', $this->destination->addDocumentPrefix($destDocumentName));
        }
        $newValue = str_replace($sourcePatterns, $destinationPatters, $recordToHandle->getValue($this->field));
        $recordToHandle->setValue($this->field, $newValue);
    }
}
