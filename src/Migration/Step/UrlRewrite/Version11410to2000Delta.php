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
use Migration\Step\UrlRewrite\Model\TemporaryTable;
use Migration\Step\UrlRewrite\Model\Version191to2000\Transformer;
use Migration\Step\UrlRewrite\Model\Version11410to2000\ProductRewritesWithoutCategories;
use Migration\Step\UrlRewrite\Model\Version11410to2000\ProductRewritesIncludedIntoCategories;
use Migration\Step\UrlRewrite\Model\Version11410to2000\CategoryRewrites;
use Migration\Step\UrlRewrite\Model\Version11410to2000\CmsPageRewrites;
use Migration\Step\UrlRewrite\Model\Version11410to2000\RedirectsRewrites;

/**
 * Class Delta
 */
class Version11410to2000Delta extends AbstractDelta
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
     * @var TemporaryTable
     */
    private $temporaryTable;

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
     * @param Transformer $transformer
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
        ProductRewritesWithoutCategories $productRewritesWithoutCategories,
        ProductRewritesIncludedIntoCategories $productRewritesIncludedIntoCategories,
        CategoryRewrites $categoryRewrites,
        CmsPageRewrites $cmsPageRewrites,
        RedirectsRewrites $redirectsRewrites
    ) {
        $this->transformer = $transformer;
        $this->temporaryTable = $temporaryTable;
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
            $this->saveCmsPageRewrites();
            $this->temporaryTable->migrateRewrites();
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
        $removedIds = [];
        $page = 0;
        $getProcessed = $documentName == 'catalog_category_product' ? true : false;
        $this->destination->getAdapter()->setForeignKeyChecks(1);
        while (!empty($items = $this->source->getDeletedRecords($documentName, $idKeys, $page++, $getProcessed))) {
            echo('.');
            $urlRewriteIds = $this->getUrlRewriteIds($documentName, $items);
            $urlRedirectIds = $this->getUrlRedirectIds($documentName, $items);
            $removedIds = array_merge(
                $removedIds,
                $this->removeChangedUrlRewrites($urlRewriteIds),
                $this->removeChangedUrlRedirects($urlRedirectIds)
            );
            $this->removeChangedUrlRewritesDestination($removedIds);
            $documentNameDelta = $this->source->getDeltaLogName($documentName);
            $documentNameDelta = $this->source->addDocumentPrefix($documentNameDelta);
            $this->markRecordsProcessed($documentNameDelta, $idKeys, $items);
        }
        $this->destination->getAdapter()->setForeignKeyChecks(0);
    }

    /**
     * @inheritdoc
     */
    public function processChangedRecords($documentName, $idKeys)
    {
        $removedIds = [];
        $page = 0;
        $getProcessed = $documentName == 'catalog_category_product' ? true : false;
        while (!empty($items = $this->source->getChangedRecords($documentName, $idKeys, $page++, $getProcessed))) {
            echo('.');
            $urlRewriteIds = $this->getUrlRewriteIds($documentName, $items);
            $urlRedirectIds = $this->getUrlRedirectIds($documentName, $items);
            $removedIds = array_merge(
                $removedIds,
                $this->removeChangedUrlRewrites($urlRewriteIds),
                $this->removeChangedUrlRedirects($urlRedirectIds)
            );
            if($urlRewriteIds) {
                $this->collectProductRewrites($urlRewriteIds);
                $this->categoryRewrites->collectRewrites($urlRewriteIds);
                $this->redirectsRewrites->collectRewrites($urlRewriteIds);

            }
            if($urlRedirectIds) {
                $this->redirectsRewrites->collectRedirects($urlRedirectIds);
            }
            $removedIds = array_merge($removedIds, $this->redirectsRewrites->removeDuplicatedUrlRedirects());
            $this->removeChangedUrlRewritesDestination($removedIds);
            $documentNameDelta = $this->source->getDeltaLogName($documentName);
            $documentNameDelta = $this->source->addDocumentPrefix($documentNameDelta);
            $this->markRecordsProcessed($documentNameDelta, $idKeys, $items);
            $this->urlRewritesChangedFlag = true;
        };
    }

    /**
     * Fulfill temporary table with product url rewrites
     *
     * @param array $urlRewriteIds
     */
    protected function collectProductRewrites(array $urlRewriteIds = [])
    {
        /** @var \Magento\Framework\DB\Adapter\Pdo\Mysql $adapter */
        $adapter = $this->source->getAdapter()->getSelect()->getAdapter();
        $queryExecute = function ($queries) use ($adapter) {
            foreach ($queries as $query) {
                $adapter->query($query);
            }
        };
        $queryExecute(
            $this->productRewritesWithoutCategories->getQueryProductsSavedForDefaultScope($urlRewriteIds)
        );
        $queryExecute(
            $this->productRewritesWithoutCategories->getQueryProductsSavedForParticularStoreView($urlRewriteIds)
        );
        $queryExecute(
            $this->productRewritesIncludedIntoCategories->getQueryProductsSavedForDefaultScope($urlRewriteIds)
        );
        $queryExecute(
            $this->productRewritesIncludedIntoCategories->getQueryProductsSavedForParticularStoreView($urlRewriteIds)
        );
    }

    /**
     * Save Cms Page Rewrites
     */
    private function saveCmsPageRewrites()
    {
        /** @var \Magento\Framework\DB\Adapter\Pdo\Mysql $adapter */
        $adapter = $this->source->getAdapter()->getSelect()->getAdapter();
        $adapter->delete(
            $this->source->addDocumentPrefix($this->temporaryTable->getName()),
            "entity_type = 'cms-page'"
        );
        /** @var \Magento\Framework\DB\Adapter\Pdo\Mysql $adapter */
        $adapter = $this->destination->getAdapter()->getSelect()->getAdapter();
        $adapter->delete(
            $this->destination->addDocumentPrefix(Version11410to2000::DESTINATION),
            "entity_type = 'cms-page'"
        );
        $this->cmsPageRewrites->collectRewrites();
    }

    /**
     * Remove changed url rewrites in temporary table
     *
     * @param array $urlRewriteIds
     * @return array
     */
    private function removeChangedUrlRewrites(array $urlRewriteIds)
    {
        $urlRewriteIdsToRemove = [];
        if ($urlRewriteIds) {
            /** @var \Magento\Framework\DB\Select $select */
            $select = $this->source->getAdapter()->getSelect();
            $select->from(['r' => $this->source->addDocumentPrefix($this->temporaryTable->getName())], ['id']);
            $select->where('r.url_rewrite_id in (?)', $urlRewriteIds);
            $urlRewriteIdsToRemove = $select->getAdapter()->fetchCol($select);
            $this->source->deleteRecords(
                $this->source->addDocumentPrefix($this->temporaryTable->getName()),
                'id',
                $urlRewriteIdsToRemove
            );
        }
        return $urlRewriteIdsToRemove;
    }

    /**
     * Remove changed url rewrites in destination table
     *
     * @param array $urlRewriteIds
     */
    private function removeChangedUrlRewritesDestination(array $urlRewriteIds)
    {
        if ($urlRewriteIds) {
            $this->destination->deleteRecords(
                $this->destination->addDocumentPrefix(Version11410to2000::DESTINATION),
                'url_rewrite_id',
                $urlRewriteIds
            );
        }
    }

    /**
     * Remove changed url redirects in temporary table
     *
     * @param array $redirectIds
     * @return array
     */
    private function removeChangedUrlRedirects(array $redirectIds)
    {
        $redirectIdsToRemove = [];
        if ($redirectIds) {
            /** @var \Magento\Framework\DB\Select $select */
            $select = $this->source->getAdapter()->getSelect();
            $select->from(['r' => $this->source->addDocumentPrefix($this->temporaryTable->getName())], ['id']);
            $select->where('r.redirect_id in (?)', $redirectIds);
            $redirectIdsToRemove = $select->getAdapter()->fetchCol($select);
            $this->source->deleteRecords(
                $this->source->addDocumentPrefix($this->temporaryTable->getName()),
                'id',
                $redirectIdsToRemove
            );
        }
        return $redirectIdsToRemove;
    }

    /**
     * Get url rewrite ids
     *
     * @param $document
     * @param $items
     * @return array
     */
    private function getUrlRewriteIds($document, $items)
    {
        if ($document == 'enterprise_url_rewrite') {
            $urlRewriteIds = array_column($items, 'url_rewrite_id');
            return $urlRewriteIds;
        } elseif ($document == 'catalog_category_product') {
            $productIds = array_column($items, 'product_id');
            /** @var \Magento\Framework\DB\Select $select */
            $select = $this->source->getAdapter()->getSelect();
            $select->from(['cpeuk' => $this->source->addDocumentPrefix('catalog_product_entity_url_key')], []);
            $select->join(
                ['eur' => $this->source->addDocumentPrefix('enterprise_url_rewrite')],
                'eur.value_id = cpeuk.value_id',
                ['url_rewrite_id']
            );
            $select->where('cpeuk.entity_id in (?)', $productIds);
            $urlRewriteIds = $select->getAdapter()->fetchCol($select);
            return $urlRewriteIds;
        }
        return [];
    }

    /**
     * Get url redirect ids
     *
     * @param $document
     * @param $items
     * @return array
     */
    private function getUrlRedirectIds($document, $items)
    {
        if ($document == 'enterprise_url_rewrite_redirect') {
            $urlRedirectIds = array_column($items, 'redirect_id');
            return $urlRedirectIds;
        }
        return [];
    }
}
