<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Map;

use Migration\Handler;
use Migration\Logger\Logger;
use Migration\MapReader;
use Migration\Resource;

class Volume
{
    /**
     * @var Resource\Source
     */
    protected $source;

    /**
     * @var Resource\Destination
     */
    protected $destination;

    /**
     * @var MapReader
     */
    protected $mapReader;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param Logger $logger
     * @param Resource\Source $source
     * @param Resource\Destination $destination
     * @param MapReader $mapReader
     */
    public function __construct(
        Logger $logger,
        Resource\Source $source,
        Resource\Destination $destination,
        MapReader $mapReader
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->mapReader = $mapReader;
        $this->mapReader->init();
        $this->logger =  $logger;
    }

    /**
     * @return bool
     */
    public function perform()
    {
        $isSuccess = true;
        $sourceDocuments = $this->source->getDocumentList();
        foreach ($sourceDocuments as $sourceDocName) {
            $destinationName = $this->mapReader->getDocumentMap($sourceDocName, MapReader::TYPE_SOURCE);
            if (!$destinationName) {
                continue;
            }
            $sourceCount = $this->source->getRecordsCount($sourceDocName);
            $destinationCount = $this->destination->getRecordsCount($destinationName);
            if ($sourceCount != $destinationCount) {
                $isSuccess = false;
                $this->logger->error(sprintf(
                    PHP_EOL . 'Volume check failed for the destination document %s',
                    PHP_EOL . $destinationName
                ));
            }
        }
        return $isSuccess;
    }
}
