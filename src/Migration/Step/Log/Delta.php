<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Log;

use Migration\App\Step\AbstractDelta;
use Migration\Logger\Logger;
use Migration\MapReader\MapReaderLog;
use Migration\Resource\Source;
use Migration\Resource;

class Delta extends AbstractDelta
{
    /**
     * @param Source $source
     * @param MapReaderLog $mapReader
     * @param Logger $logger
     * @param Resource\Destination $destination
     * @param Resource\RecordFactory $recordFactory
     * @param \Migration\RecordTransformerFactory $recordTransformerFactory
     */
    public function __construct(
        Source $source,
        MapReaderLog $mapReader,
        Logger $logger,
        Resource\Destination $destination,
        Resource\RecordFactory $recordFactory,
        \Migration\RecordTransformerFactory $recordTransformerFactory
    ) {
        parent::__construct($source, $mapReader, $logger, $destination, $recordFactory, $recordTransformerFactory);
    }
}
