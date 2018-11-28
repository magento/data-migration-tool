<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\SalesOrder;

use Migration\App\Step\AbstractVolume;
use Migration\Logger\Logger;
use Migration\Reader\Map;
use Migration\Reader\MapFactory;
use Migration\Reader\MapInterface;
use Migration\ResourceModel\Destination;
use Migration\ResourceModel\Source;
use Migration\App\ProgressBar;

/**
 * Class Volume
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Volume extends AbstractVolume
{
    /**
     * @var InitialData
     */
    protected $initialData;

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
        parent::__construct($logger);
    }

    /**
     * @inheritdoc
     */
    public function perform()
    {
        $sourceDocuments = array_keys($this->helper->getDocumentList());
        $this->progress->start(count($sourceDocuments));
        foreach ($sourceDocuments as $sourceDocName) {
            $this->progress->advance();
            $destinationName = $this->map->getDocumentMap($sourceDocName, MapInterface::TYPE_SOURCE);
            if (!$destinationName) {
                continue;
            }
            $this->checkMapEntities($sourceDocName, $destinationName);
            $this->checkEavEntities();
        }
        $this->progress->finish();
        return $this->checkForErrors();
    }

    /**
     * Check map entities
     *
     * @param string $sourceDocName
     * @param string $destinationName
     * @return void
     */
    protected function checkMapEntities($sourceDocName, $destinationName)
    {
        $sourceCount = $this->source->getRecordsCount($sourceDocName);
        $destinationCount = $this->destination->getRecordsCount($destinationName);
        if ($sourceCount != $destinationCount) {
            $this->errors[] = sprintf(
                'Mismatch of entities in the document: %s Source: %s Destination: %s',
                $destinationName,
                $sourceCount,
                $destinationCount
            );
        }
    }

    /**
     * Check eav entities
     *
     * @return void
     */
    protected function checkEavEntities()
    {
        $countBeforeRun = $this->initialData->getDestEavAttributesCount($this->helper->getDestEavDocument());
        $countAfterRun = $this->destination->getRecordsCount($this->helper->getDestEavDocument());
        $countEavAttributes = null;
        foreach ($this->helper->getEavAttributes() as $eavAttribute) {
            $countEavAttributes += count($this->helper->getSourceAttributes($eavAttribute));
        }
        if (($countBeforeRun + $countEavAttributes) != $countAfterRun) {
            $this->errors[] = sprintf(
                'Mismatch of entities in the document: %s',
                $this->helper->getDestEavDocument()
            );
        }
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
