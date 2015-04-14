<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Eav;

use Migration\MapReaderInterface;
use Migration\MapReader\MapReaderEav;
use Migration\RecordTransformer;
use Migration\RecordTransformerFactory;
use Migration\Resource\Destination;
use Migration\Resource\Document;
use Migration\Resource\Source;

/**
 * Class Helper
 */
class Helper
{
    /**
     * @var MapReaderEav
     */
    protected $map;

    /**
     * @var Destination
     */
    protected $destination;

    /**
     * @var RecordTransformerFactory
     */
    protected $factory;

    /**
     * @param MapReaderEav $mapReader
     * @param Source $source
     * @param Destination $destination
     * @param RecordTransformerFactory $factory
     */
    public function __construct(
        MapReaderEav $mapReader,
        Source $source,
        Destination $destination,
        RecordTransformerFactory $factory
    ) {
        $this->map = $mapReader;
        $this->source = $source;
        $this->destination = $destination;
        $this->factory = $factory;
    }

    /**
     * @param string $sourceDocumentName
     * @return int
     */
    public function getSourceRecordsCount($sourceDocumentName)
    {
        return $this->source->getRecordsCount($sourceDocumentName);
    }

    /**
     * @param string $sourceDocumentName
     * @return int
     */
    public function getDestinationRecordsCount($sourceDocumentName)
    {
        return $this->destination->getRecordsCount(
            $this->map->getDocumentMap($sourceDocumentName, MapReaderInterface::TYPE_SOURCE)
        );
    }

    /**
     * @param string $sourceDocName
     * @param array $keyFields
     * @return array
     */
    public function getDestinationRecords($sourceDocName, $keyFields = [])
    {
        $destinationDocumentName = $this->map->getDocumentMap($sourceDocName, MapReaderInterface::TYPE_SOURCE);
        $data = [];
        $count = $this->destination->getRecordsCount($destinationDocumentName);
        foreach ($this->destination->getRecords($destinationDocumentName, 0, $count) as $row) {
            if ($keyFields) {
                $key = [];
                foreach ($keyFields as $keyField) {
                    $key[] = $row[$keyField];
                }
                $data[implode('-', $key)] = $row;
            } else {
                $data[] = $row;
            }
        }

        return $data;
    }

    /**
     * @param string $sourceDocName
     * @param array $keyFields
     * @return array
     */
    public function getSourceRecords($sourceDocName, $keyFields = [])
    {
        $data = [];
        $count = $this->source->getRecordsCount($sourceDocName);
        foreach ($this->source->getRecords($sourceDocName, 0, $count) as $row) {
            if ($keyFields) {
                $key = [];
                foreach ($keyFields as $keyField) {
                    $key[] = $row[$keyField];
                }
                $data[implode('-', $key)] = $row;
            } else {
                $data[] = $row;
            }
        }

        return $data;
    }

    /**
     * @param Document $sourceDocument
     * @param Document $destinationDocument
     * @return RecordTransformer
     */
    public function getRecordTransformer($sourceDocument, $destinationDocument)
    {
        return $this->factory->create([
            'sourceDocument' => $sourceDocument,
            'destDocument' => $destinationDocument,
            'mapReader' => $this->map
        ])->init();
    }
}
