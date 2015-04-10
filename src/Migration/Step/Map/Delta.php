<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Map;

use Migration\App\Step\AbstractDelta;
use Migration\Logger\Logger;
use Migration\MapReader\MapReaderChangelog;
use Migration\MapReader\MapReaderMain;
use Migration\Resource\Source;
use Migration\Resource;

class Delta extends AbstractDelta
{
    /**
     * @var Migrate
     */
    protected $migrate;

    /**
     * @param Source $source
     * @param MapReaderChangelog $mapReader
     * @param Logger $logger
     * @param Resource\Destination $destination
     * @param Resource\RecordFactory $recordFactory
     * @param \Migration\RecordTransformerFactory $recordTransformerFactory
     * @param Migrate $migrate
     */
    public function __construct(
        Source $source,
        MapReaderChangelog $mapReader,
        Logger $logger,
        Resource\Destination $destination,
        Resource\RecordFactory $recordFactory,
        \Migration\RecordTransformerFactory $recordTransformerFactory,
        Migrate $migrate
    ) {
        $this->migrate = $migrate;
        parent::__construct($source, $mapReader, $logger, $destination, $recordFactory, $recordTransformerFactory);
    }

    /**
     * @param Resource\Document $sourceDocument
     * @param Resource\Document $destinationDocument
     * @return \Migration\RecordTransformer
     */
    protected function getRecordTransformer($sourceDocument, $destinationDocument)
    {
        return $this->migrate->getRecordTransformer($sourceDocument, $destinationDocument);
    }
}
