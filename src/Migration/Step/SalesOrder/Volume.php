<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\SalesOrder;

use Migration\App\Step\StageInterface;
use Migration\Logger\Logger;
use Migration\Reader\Map;
use Migration\Reader\MapFactory;
use Migration\Reader\MapInterface;
use Migration\Resource\Destination;
use Migration\Resource\Source;
use Migration\App\ProgressBar;
use Migration\Logger\Manager as LogManager;

/**
 * Class Volume
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Volume implements StageInterface
{
    /**
     * @var InitialData
     */
    protected $initialData;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var ProgressBar\LogLevelProcessor
     */
    protected $progress;

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
     * @var Helper
     */
    protected $helper;

    /**
     * @param Source $source
     * @param Destination $destination
     * @param InitialData $initialData
     * @param Helper $helper
     * @param MapFactory $mapFactory
     * @param ProgressBar\LogLevelProcessor $progress
     * @param Logger $logger
     */
    public function __construct(
        Source $source,
        Destination $destination,
        InitialData $initialData,
        Helper $helper,
        MapFactory $mapFactory,
        ProgressBar\LogLevelProcessor $progress,
        Logger $logger
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->initialData = $initialData;
        $this->helper = $helper;
        $this->map = $mapFactory->create('sales_order_map_file');
        $this->progress = $progress;
        $this->logger = $logger;
    }

    /**
     * @return bool
     */
    public function perform()
    {
        $isSuccess = true;
        $sourceDocuments = array_keys($this->helper->getDocumentList());
        $this->progress->start(count($sourceDocuments), LogManager::LOG_LEVEL_INFO);
        foreach ($sourceDocuments as $sourceDocName) {
            $this->progress->advance(LogManager::LOG_LEVEL_INFO);
            $destinationName = $this->map->getDocumentMap($sourceDocName, MapInterface::TYPE_SOURCE);
            if (!$destinationName) {
                continue;
            }
            $mapIsSuccess = $this->checkMapEntities($sourceDocName, $destinationName);
            $eavIsSuccess = $this->checkEavEntities($sourceDocName, $destinationName);
            if (!$mapIsSuccess || !$eavIsSuccess) {
                $isSuccess = false;
                break;
            }
        }
        $this->progress->finish(LogManager::LOG_LEVEL_INFO);
        return (bool)$isSuccess;
    }

    /**
     * @param string $sourceDocName
     * @param string $destinationName
     * @return bool
     */
    protected function checkMapEntities($sourceDocName, $destinationName)
    {
        $isSuccess = true;
        $sourceCount = $this->source->getRecordsCount($sourceDocName);
        $destinationCount = $this->destination->getRecordsCount($destinationName);
        if ($sourceCount != $destinationCount) {
            $isSuccess = false;
            $this->logger->error(sprintf(
                'Volume check failed for the destination document %s',
                $destinationName
            ));
        }
        return $isSuccess;
    }

    /**
     * @return bool
     */
    protected function checkEavEntities()
    {
        $isSuccess = true;
        $countBeforeRun = $this->initialData->getDestEavAttributesCount($this->helper->getDestEavDocument());
        $countAfterRun = $this->destination->getRecordsCount($this->helper->getDestEavDocument());
        $countEavAttributes = null;
        foreach ($this->helper->getEavAttributes() as $eavAttribute) {
            $countEavAttributes += count($this->helper->getSourceAttributes($eavAttribute));
        }
        if (($countBeforeRun + $countEavAttributes) != $countAfterRun) {
            $isSuccess = false;
            $this->logger->error(sprintf(
                'Volume check failed for the destination document %s',
                $this->helper->getDestEavDocument()
            ));
        }
        return $isSuccess;
    }

    /**
     * @param string $message
     * @return void
     */
    protected function logError($message)
    {
        $this->logger->log(Logger::ERROR, $message);
    }

    /**
     * Get iterations count for step
     *
     * @return int
     */
    protected function getIterationsCount()
    {
        $migrationDocuments = $this->helper->getDocumentList();
        $documents = [
            $this->helper->getDestEavDocument(),
            array_keys($migrationDocuments),
            array_values($migrationDocuments)
        ];
        return count($documents);
    }
}
