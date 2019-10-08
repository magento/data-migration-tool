<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\UrlRewrite;

use Migration\App\Step\AbstractDelta;
use Migration\Logger\Logger;
use Migration\Reader\GroupsFactory;
use Migration\Reader\MapFactory;
use Migration\ResourceModel\Source;
use Migration\ResourceModel\Destination;
use Migration\ResourceModel;
use Migration\Step\UrlRewrite\Model\VersionCommerce\TemporaryTable;
use Migration\Step\UrlRewrite\Model\VersionCommerce\TableName;
use Migration\Step\UrlRewrite\Model\Version191to2000\Transformer;
use Migration\Step\UrlRewrite\Model\Version11300to2000\ProductRewritesWithoutCategories;
use Migration\Step\UrlRewrite\Model\Version11300to2000\ProductRewritesIncludedIntoCategories;
use Migration\Step\UrlRewrite\Model\Version11300to2000\CategoryRewrites;
use Migration\Step\UrlRewrite\Model\Version11300to2000\CmsPageRewrites;
use Migration\Step\UrlRewrite\Model\Version11300to2000\RedirectsRewrites;

/**
 * Class Delta
 */
class Version11300to2000Delta extends AbstractDelta
{
    /**
     * @var string
     */
    protected $mapConfigOption = 'map_file';

    /**
     * @var string
     */
    protected $groupName = 'delta_url_rewrite';

    /**
     * @var Transformer
     */
    private $transformer;

    /**
     * @var ProductRewritesIncludedIntoCategories
     */
    private $productRewritesIncludedIntoCategories;

    /**
     * @var ProductRewritesWithoutCategories
     */
    private $productRewritesWithoutCategories;

    /**
     * @var CategoryRewrites
     */
    private $categoryRewrites;

    /**
     * @var RedirectsRewrites
     */
    private $redirectsRewrites;

    /**
     * @var CmsPageRewrites
     */
    private $cmsPageRewrites;

    /**
     * @var TableName
     */
    protected $tableName;

    /**
     * @var TemporaryTable
     */
    protected $temporaryTable;

    /**
     * @var
     */
    private $urlRewritesChangedFlag = false;

    /**
     * @param Source $source
     * @param MapFactory $mapFactory
     * @param GroupsFactory $groupsFactory
     * @param Logger $logger
     * @param Destination $destination
     * @param ResourceModel\RecordFactory $recordFactory
     * @param \Migration\RecordTransformerFactory $recordTransformerFactory
     * @param TemporaryTable $temporaryTable
     * @param TableName $tableName
     * @param TemporaryTable $temporaryTable
     * @param ProductRewritesWithoutCategories $productRewritesWithoutCategories
     * @param ProductRewritesIncludedIntoCategories $productRewritesIncludedIntoCategories
     * @param CategoryRewrites $categoryRewrites
     * @param CmsPageRewrites $cmsPageRewrites
     * @param RedirectsRewrites $redirectsRewrites
     */
    public function __construct(
        Source $source,
        MapFactory $mapFactory,
        GroupsFactory $groupsFactory,
        Logger $logger,
        Destination $destination,
        ResourceModel\RecordFactory $recordFactory,
        \Migration\RecordTransformerFactory $recordTransformerFactory,
        Transformer $transformer,
        TemporaryTable $temporaryTable,
        TableName $tableName,
        ProductRewritesWithoutCategories $productRewritesWithoutCategories,
        ProductRewritesIncludedIntoCategories $productRewritesIncludedIntoCategories,
        CategoryRewrites $categoryRewrites,
        CmsPageRewrites $cmsPageRewrites,
        RedirectsRewrites $redirectsRewrites
    ) {
        $this->transformer = $transformer;
        $this->temporaryTable = $temporaryTable;
        $this->tableName = $tableName;
        $this->productRewritesWithoutCategories = $productRewritesWithoutCategories;
        $this->productRewritesIncludedIntoCategories = $productRewritesIncludedIntoCategories;
        $this->categoryRewrites = $categoryRewrites;
        $this->redirectsRewrites = $redirectsRewrites;
        $this->cmsPageRewrites = $cmsPageRewrites;
        parent::__construct(
            $source,
            $mapFactory,
            $groupsFactory,
            $logger,
            $destination,
            $recordFactory,
            $recordTransformerFactory
        );
    }

    /**
     * @inheritdoc
     */
    public function perform()
    {
        parent::perform();
        if ($this->urlRewritesChangedFlag) {
            $this->removeUrlRewrites();
            $this->temporaryTable->initTemporaryTable(
                $this->productRewritesWithoutCategories,
                $this->productRewritesIncludedIntoCategories,
                $this->categoryRewrites,
                $this->cmsPageRewrites,
                $this->redirectsRewrites
            );
            $this->temporaryTable->migrateRewrites();
            foreach (array_keys($this->deltaDocuments) as $documentName) {
                $this->markProcessedRewrites($documentName);
            }
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    protected function getDocumentMap($document, $type)
    {
        return $document;
    }

    /**
     * @inheritdoc
     */
    public function processDeletedRecords($documentName, $idKeys, $destinationName)
    {
        $this->urlRewritesChangedFlag = true;
    }

    /**
     * @inheritdoc
     */
    protected function processChangedRecords($documentName, $idKeys)
    {
        $this->urlRewritesChangedFlag = true;
    }

    /**
     * Remove url rewrites
     */
    private function removeUrlRewrites()
    {
        /** @var \Magento\Framework\DB\Select $select */
        $select = $this->source->getAdapter()->getSelect();
        $select->from(['r' => $this->source->addDocumentPrefix($this->temporaryTable->getName())], ['id']);
        $urlRewriteIdsToRemove = $select->getAdapter()->fetchCol($select);
        $this->destination->deleteRecords(
            $this->destination->addDocumentPrefix($this->tableName->getDestinationTableName()),
            'url_rewrite_id',
            $urlRewriteIdsToRemove
        );
        $this->destination->deleteRecords(
            $this->destination->addDocumentPrefix($this->tableName->getDestinationProductCategoryTableName()),
            'url_rewrite_id',
            $urlRewriteIdsToRemove
        );
    }

    /**
     * Mark processed rewrites
     *
     * @param string $documentName
     */
    private function markProcessedRewrites($documentName)
    {
        $documentNameDelta = $this->source->getDeltaLogName($documentName);
        $documentNameDelta = $this->source->addDocumentPrefix($documentNameDelta);
        /** @var ResourceModel\Adapter\Mysql $adapter */
        $adapter = $this->source->getAdapter();
        $adapter->updateDocument($documentNameDelta, ['processed' => 1]);
    }
}
