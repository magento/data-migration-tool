<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\OrderGridsEE;

use Migration\Logger\Logger;
use Migration\Reader\GroupsFactory;
use Migration\ResourceModel\Destination;
use Migration\ResourceModel\Source;

class Delta extends \Migration\Step\OrderGrids\Delta
{
    /**
     * @param Source $source
     * @param Destination $destination
     * @param GroupsFactory $groupsFactory
     * @param Logger $logger
     * @param Helper $helper
     * @param Data $data
     */
    public function __construct(
        Source $source,
        Destination $destination,
        GroupsFactory $groupsFactory,
        Logger $logger,
        Helper $helper,
        Data $data
    ) {
        $this->source = $source;
        $this->readerGroups = $groupsFactory->create('order_grids_document_groups_file');
        $this->logger = $logger;
        $this->helper = $helper;
        $this->data = $data;
        parent::__construct($source, $destination, $groupsFactory, $logger, $helper, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function perform()
    {
        parent::perform();

        $documentMap = $this->helper->getDocumentList();
        $sourceDocuments = $this->readerGroups->getGroup('archive_orders');
        foreach ($sourceDocuments as $sourceDocName => $idKey) {
            if ($this->source->getRecordsCount($this->source->getDeltaLogName($sourceDocName)) == 0) {
                continue;
            }
            $items = $this->source->getDeletedRecords($sourceDocName, $idKey, 0, true);
            if (empty($items)) {
                continue;
            }
            $this->logger->debug(sprintf('%s has changes', $sourceDocName));

            if (!$this->eolOnce) {
                $this->eolOnce = true;
                echo PHP_EOL;
            }
            $page = 1;

            $destinationDocument = $documentMap[$sourceDocName];
            do {
                $this->destination->deleteRecords(
                    $this->destination->addDocumentPrefix($destinationDocument),
                    $idKey,
                    $items
                );
                $documentNameDelta = $this->source->getDeltaLogName($sourceDocName);
                $documentNameDelta = $this->source->addDocumentPrefix($documentNameDelta);
                $this->markRecordsProcessed($documentNameDelta, $idKey, $items);
            } while (!empty($items = $this->source->getChangedRecords($sourceDocName, $idKey, $page++)));
        }
        return true;
    }
}
