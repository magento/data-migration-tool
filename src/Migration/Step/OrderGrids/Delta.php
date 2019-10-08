<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\OrderGrids;

use Migration\App\Step\StageInterface;
use Migration\Logger\Logger;
use Migration\Reader\GroupsFactory;
use Migration\ResourceModel\Source;
use Migration\ResourceModel\Destination;
use Migration\ResourceModel;

/**
 * Class Delta
 */
class Delta implements StageInterface
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
     * @var Logger
     */
    protected $logger;

    /**
     * @var \Migration\Reader\Groups
     */
    protected $readerGroups;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var Data
     */
    protected $data;

    /**
     * @var bool
     */
    protected $eolOnce = false;

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
        $this->destination = $destination;
        $this->readerGroups = $groupsFactory->create('order_grids_document_groups_file');
        $this->logger = $logger;
        $this->helper = $helper;
        $this->data = $data;
    }

    /**
     * @inheritdoc
     */
    public function perform()
    {
        $updateData = $this->helper->getUpdateData();
        $selectData = $this->helper->getSelectData();
        $sourceDocuments = $this->readerGroups->getGroup('source_documents');
        foreach ($sourceDocuments as $sourceDocName => $idKey) {
            if ($this->source->getRecordsCount($this->source->getDeltaLogName($sourceDocName)) == 0) {
                continue;
            }
            $items = $this->source->getChangedRecords($sourceDocName, [$idKey], 0, true);
            if (empty($items)) {
                continue;
            }
            $this->logger->debug(sprintf('%s has changes', $sourceDocName));

            if (!$this->eolOnce) {
                $this->eolOnce = true;
                echo PHP_EOL;
            }
            $gridIdKey = $updateData[$sourceDocName]['idKey'];
            $page = 1;
            do {
                $ids = [];
                foreach ($items as $data) {
                    echo('.');
                    $ids[] = $data[$gridIdKey];
                }
                foreach ($updateData[$sourceDocName]['methods'] as $method) {
                    echo('.');
                    $destinationDocumentName = $selectData[$method]['destination'];
                    $select = call_user_func_array([$this->data, $method], [$selectData[$method]['columns'], $ids]);
                    $this->destination->getAdapter()->insertFromSelect(
                        $select,
                        $this->destination->addDocumentPrefix($destinationDocumentName),
                        [],
                        \Magento\Framework\Db\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
                    );
                }
                $documentNameDelta = $this->source->getDeltaLogName($sourceDocName);
                $documentNameDelta = $this->source->addDocumentPrefix($documentNameDelta);
                $this->markRecordsProcessed($documentNameDelta, $idKey, $ids);
            } while (!empty($items = $this->source->getChangedRecords($sourceDocName, [$idKey], $page++)));
        }
        return true;
    }

    /**
     * Mark processed records for deletion
     *
     * @param string $documentName
     * @param string $idKey
     * @param [] $ids
     * @return void
     */
    protected function markRecordsProcessed($documentName, $idKey, $ids)
    {
        $ids = implode("','", $ids);
        /** @var ResourceModel\Adapter\Mysql $adapter */
        $adapter = $this->source->getAdapter();
        $adapter->updateDocument($documentName, ['processed' => 1], "`$idKey` in ('$ids')");
    }
}
