<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Map;

use Migration\App\Step\AbstractDelta;
use Migration\Logger\Logger;
use Migration\MapReader\MapReaderDeltalog;
use Migration\MapReader\MapReaderMain;
use Migration\Resource\Source;
use Migration\Resource\Destination;
use Migration\Resource;

class Delta extends AbstractDelta
{
    /**
     * @var Data
     */
    protected $data;

    /**
     * @param Source $source
     * @param MapReaderMain $mapReader
     * @param Logger $logger
     * @param Destination $destination
     * @param Resource\RecordFactory $recordFactory
     * @param \Migration\RecordTransformerFactory $recordTransformerFactory
     * @param Data $data
     */
    public function __construct(
        Source $source,
        MapReaderMain $mapReader,
        Logger $logger,
        Destination $destination,
        Resource\RecordFactory $recordFactory,
        \Migration\RecordTransformerFactory $recordTransformerFactory,
        Data $data
    ) {
        $this->data = $data;
        parent::__construct($source, $mapReader, $logger, $destination, $recordFactory, $recordTransformerFactory);
    }

    /**
     * @param Resource\Document $sourceDocument
     * @param Resource\Document $destinationDocument
     * @return \Migration\RecordTransformer
     */
    protected function getRecordTransformer($sourceDocument, $destinationDocument)
    {
        return $this->data->getRecordTransformer($sourceDocument, $destinationDocument);
    }
}
