<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\Rule;

use Migration\Handler\AbstractHandler;
use Migration\MapReader\MapReaderMain;
use Migration\MapReaderInterface;
use Migration\Resource\Destination;
use Migration\Resource\Record;
use Migration\Resource\Source;

/**
 * Class ConditionSql
 */
class ConditionSql extends AbstractHandler
{
    /**
     * @var MapReaderMain
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
     * @param MapReaderMain $mapReader
     * @param Source $source
     * @param Destination $destination
     */
    public function __construct(MapReaderMain $mapReader, Source $source, Destination $destination)
    {
        $this->map = $mapReader;
        $this->source = $source;
        $this->destination = $destination;
    }

    /**
     * @param Record $recordToHandle
     * @param Record $oppositeRecord
     * @return mixed
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        $sourcePatterns = [];
        $destinationPatters = [];
        foreach ($this->source->getDocumentList() as $document) {
            $destDocumentName = $this->map->getDocumentMap($document, MapReaderInterface::TYPE_SOURCE);
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
