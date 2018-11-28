<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\TierPrice;

use Migration\App\Step\AbstractVolume;
use Migration\ResourceModel;
use Migration\App\ProgressBar;
use Migration\Logger\Logger;

/**
 * Class Volume
 */
class Volume extends AbstractVolume
{
    /**
     * @var ResourceModel\Source
     */
    protected $source;

    /**
     * @var ResourceModel\Destination
     */
    protected $destination;

    /**
     * @var ProgressBar\LogLevelProcessor
     */
    protected $progress;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param ProgressBar\LogLevelProcessor $progress
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     * @param Helper $helper
     * @param Logger $logger
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progress,
        ResourceModel\Source $source,
        ResourceModel\Destination $destination,
        Helper $helper,
        Logger $logger
    ) {
        $this->progress = $progress;
        $this->source = $source;
        $this->destination = $destination;
        $this->helper = $helper;
        parent::__construct($logger);
    }

    /**
     * @inheritdoc
     */
    public function perform()
    {
        $sourceDocuments = $this->helper->getSourceDocuments();
        $destinationDocuments = $this->helper->getDestinationDocuments();
        $this->progress->start(count($sourceDocuments) + count($destinationDocuments));

        $destinationTotal = 0;
        $sourceTotal = $this->getSourceTotal($sourceDocuments);
        foreach ($destinationDocuments as $documentName) {
            $destinationTotal += $this->destination->getRecordsCount($documentName);
            $this->progress->advance();
        }

        if ($sourceTotal != $destinationTotal) {
            $this->errors[] = 'Mismatch of amount of entities in documents';
        }

        $this->progress->finish();
        return $this->checkForErrors(Logger::ERROR);
    }

    /**
     * Return number of records with unique key taken into account. Duplicated records will be omitted
     *
     * @param array $sourceDocuments
     * @return int
     */
    private function getSourceTotal(array $sourceDocuments)
    {
        $sourceRecordsUnique = [];
        foreach ($sourceDocuments as $documentName) {
            $this->progress->advance();
            $sourceRecords = $this->source->getRecords(
                $documentName,
                0,
                $this->source->getRecordsCount($documentName)
            );
            foreach ($sourceRecords as $record) {
                $record['qty'] = isset($record['qty']) ? $record['qty'] : '1.0000';
                $key = $record['entity_id'] . '-' .
                    $record['all_groups'] . '-' .
                    $record['customer_group_id'] . '-' .
                    $record['qty'] . '-' .
                    $record['website_id'];
                $sourceRecordsUnique[$key] = $record;
            }
        }
        return count($sourceRecordsUnique);
    }
}
