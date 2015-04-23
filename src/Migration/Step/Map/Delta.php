<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Map;

use Migration\App\Step\AbstractDelta;
use Migration\Logger\Logger;
use Migration\Reader\MapFactory;
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
     * @param MapFactory $mapFactory
     * @param Logger $logger
     * @param Destination $destination
     * @param Resource\RecordFactory $recordFactory
     * @param \Migration\RecordTransformerFactory $recordTransformerFactory
     * @param Data $data
     * @param string $mapConfigOption
     */
    public function __construct(
        Source $source,
        MapFactory $mapFactory,
        Logger $logger,
        Destination $destination,
        Resource\RecordFactory $recordFactory,
        \Migration\RecordTransformerFactory $recordTransformerFactory,
        Data $data,
        $mapConfigOption = 'map_file'
    ) {
        $this->data = $data;
        parent::__construct(
            $source,
            $mapFactory,
            $logger,
            $destination,
            $recordFactory,
            $recordTransformerFactory,
            $mapConfigOption
        );
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
