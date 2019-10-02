<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\UrlRewrite\Model\VersionCommerce;

use Migration\ResourceModel\Source;
use Migration\ResourceModel\Adapter\Mysql as AdapterMysql;
use Migration\Reader\MapInterface;
use Migration\ResourceModel\Document;
use Migration\Step\UrlRewrite\Helper;
use Migration\ResourceModel\Record\Collection;
use Migration\Step\UrlRewrite\Model\Suffix;

/**
 * Class TemporaryTable creates a table where all url rewrites will be collected
 */
class TemporaryTable
{
    /**
     * @var AdapterMysql
     */
    private $sourceAdapter;

    /**
     * @var TableName
     */
    private $tableName;

    /**
     * @var array
     */
    protected $duplicateIndex;

    /**
     * @var array
     */
    protected $resolvedDuplicates = [];

    /**
     * ResourceModel of source
     *
     * @var \Migration\ResourceModel\Source
     */
    protected $source;

    /**
     * ResourceModel of destination
     *
     * @var \Migration\ResourceModel\Destination
     */
    protected $destination;

    /**
     * Record Factory
     *
     * @var \Migration\ResourceModel\RecordFactory
     */
    protected $recordFactory;

    /**
     * Record Collection Factory
     *
     * @var \Migration\ResourceModel\Record\CollectionFactory
     */
    protected $recordCollectionFactory;

    /**
     * LogLevelProcessor instance
     *
     * @var \Migration\App\ProgressBar\LogLevelProcessor
     */
    protected $progress;

    /**
     * Logger instance
     *
     * @var \Migration\Logger\Logger
     */
    protected $logger;

    /**
     * @var bool
     */
    protected static $dataInitialized = false;

    /**
     * @var array
     */
    protected $suffixData;

    /**
     * @var \Migration\Step\UrlRewrite\Helper
     */
    protected $helper;

    /**
     * @var ProductRewritesIncludedIntoCategoriesInterface
     */
    private $productRewritesIncludedIntoCategories;

    /**
     * @var ProductRewritesWithoutCategoriesInterface
     */
    private $productRewritesWithoutCategories;

    /**
     * @var CategoryRewritesInterface
     */
    private $categoryRewrites;

    /**
     * @var RedirectsRewritesInterface
     */
    private $redirectsRewrites;

    /**
     * @var CmsPageRewritesInterface
     */
    private $cmsPageRewrites;

    /**
     * @var Suffix
     */
    private $suffix;

    /**
     * @var string[]
     */
    private $resultMessages = [];

    /**
     * @param \Migration\App\ProgressBar\LogLevelProcessor $progress
     * @param \Migration\Logger\Logger $logger
     * @param \Migration\Config $config
     * @param Source $source
     * @param \Migration\ResourceModel\Destination $destination
     * @param \Migration\ResourceModel\Record\CollectionFactory $recordCollectionFactory
     * @param \Migration\ResourceModel\RecordFactory $recordFactory
     * @param Helper $helper
     * @param TableName $tableName
     * @param Suffix $suffix
     */
    public function __construct(
        \Migration\App\ProgressBar\LogLevelProcessor $progress,
        \Migration\Logger\Logger $logger,
        \Migration\Config $config,
        \Migration\ResourceModel\Source $source,
        \Migration\ResourceModel\Destination $destination,
        \Migration\ResourceModel\Record\CollectionFactory $recordCollectionFactory,
        \Migration\ResourceModel\RecordFactory $recordFactory,
        Helper $helper,
        TableName $tableName,
        Suffix $suffix
    ) {
        $this->progress = $progress;
        $this->logger = $logger;
        $this->source = $source;
        $this->destination = $destination;
        $this->recordCollectionFactory = $recordCollectionFactory;
        $this->recordFactory = $recordFactory;
        $this->helper = $helper;
        $this->suffix = $suffix;
        $this->configReader = $config;
        $this->sourceAdapter = $this->source->getAdapter();
        $this->tableName = $tableName;
    }

    /**
     * Return name of temporary table
     *
     * @return string
     */
    public function getName()
    {
        return $this->tableName->getTemporaryTableName();
    }

    /**
     * Crete temporary table
     *
     * @return void
     */
    public function create()
    {
        $select = $this->sourceAdapter->getSelect();
        $select->getAdapter()->dropTable($this->source->addDocumentPrefix($this->getName()));
        /** @var \Magento\Framework\DB\Ddl\Table $table */
        $table = $select->getAdapter()->newTable($this->source->addDocumentPrefix($this->getName()))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn(
                'url_rewrite_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER
            )
            ->addColumn(
                'redirect_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER
            )
            ->addColumn(
                'request_path',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                'target_path',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                'is_system',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '0']
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER
            )
            ->addColumn(
                'entity_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32
            )
            ->addColumn(
                'redirect_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0']
            )
            ->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER
            )
            ->addColumn(
                'category_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER
            )
            ->addColumn(
                'cms_page_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER
            )
            ->addColumn(
                'priority',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER
            )
            ->addIndex(
                'url_rewrite',
                ['request_path', 'target_path', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )        ;
        $select->getAdapter()->createTable($table);
    }

    /**
     * Data migration
     *
     * @return bool
     */
    public function migrateRewrites()
    {
        $this->progress->start($this->getIterationsCount());
        $sourceDocument = $this->source->getDocument($this->getName());
        $destinationDocument = $this->destination->getDocument(
            $this->tableName->getDestinationTableName()
        );
        $destProductCategory = $this->destination->getDocument(
            $this->tableName->getDestinationProductCategoryTableName()
        );
        $duplicates = $this->getDuplicatesList();
        if (!empty($duplicates) && !empty($this->configReader->getOption('auto_resolve_urlrewrite_duplicates'))
            && empty($this->duplicateIndex)
        ) {
            foreach ($duplicates as $row) {
                $this->duplicateIndex[strtolower($row['request_path'])][] = $row;
            }
        }
        $pageNumber = 0;
        while ($data = $this->source->getRecords($sourceDocument->getName(), $pageNumber)) {
            $pageNumber++;
            $records = $this->recordCollectionFactory->create();
            $destProductCategoryRecords = $destProductCategory->getRecords();
            foreach ($data as $row) {
                $this->progress->advance();
                $records->addRecord($this->recordFactory->create(['data' => $row]));
                $productCategoryRecord = $this->getProductCategoryRecord($destProductCategory, $row);
                if ($productCategoryRecord) {
                    $destProductCategoryRecords->addRecord($productCategoryRecord);
                }
            }
            $destinationRecords = $destinationDocument->getRecords();
            $this->migrateRewriteCollection($records, $destinationRecords);
            $this->destination->saveRecords($destinationDocument->getName(), $destinationRecords);
            $this->destination->saveRecords($destProductCategory->getName(), $destProductCategoryRecords);
            $this->source->setLastLoadedRecord($sourceDocument->getName(), end($data));
        }
        $this->copyEavData('catalog_category_entity_url_key', 'catalog_category_entity_varchar', 'category');
        $this->copyEavData('catalog_product_entity_url_key', 'catalog_product_entity_varchar', 'product');
        $this->progress->finish();
        foreach ($this->resultMessages as $message) {
            $this->logger->addInfo($message);
        }
        return true;
    }

    /**
     * Get product category record
     *
     * @param Document $destProductCategory
     * @param array $row
     * @return \Migration\ResourceModel\Record|null
     * @throws \Migration\Exception
     */
    private function getProductCategoryRecord(Document $destProductCategory, array $row)
    {
        $destProductCategoryRecord = null;
        if ($row['is_system'] && $row['product_id'] && $row['category_id']) {
            $destProductCategoryRecord = $this->recordFactory->create(['document' => $destProductCategory]);
            $destProductCategoryRecord->setValue('url_rewrite_id', $row['id']);
            $destProductCategoryRecord->setValue('category_id', $row['category_id']);
            $destProductCategoryRecord->setValue('product_id', $row['product_id']);
        }
        return $destProductCategoryRecord;
    }

    /**
     * Get rewrites select
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getRewritesSelect()
    {
        /** @var \Migration\ResourceModel\Adapter\Mysql $adapter */
        $adapter = $this->source->getAdapter();
        $select = $adapter->getSelect();
        $select->from(['r' => $this->source->addDocumentPrefix($this->getName())]);
        return $select;
    }

    /**
     * Migrate rewrites
     *
     * @param Collection $source
     * @param Collection $destination
     * @return void
     */
    protected function migrateRewriteCollection(Collection $source, Collection $destination)
    {
        /** @var \Migration\ResourceModel\Record $sourceRecord */
        foreach ($source as $sourceRecord) {
            /** @var \Migration\ResourceModel\Record $destinationRecord */
            $destinationRecord = $this->recordFactory->create();
            $destinationRecord->setStructure($destination->getStructure());

            $destinationRecord->setValue('url_rewrite_id', $sourceRecord->getValue('id'));
            $destinationRecord->setValue('store_id', $sourceRecord->getValue('store_id'));
            $destinationRecord->setValue('description', $sourceRecord->getValue('description'));
            $destinationRecord->setValue('redirect_type', 0);
            $destinationRecord->setValue('is_autogenerated', $sourceRecord->getValue('is_system'));
            $destinationRecord->setValue('metadata', '');
            $destinationRecord->setValue('redirect_type', $sourceRecord->getValue('redirect_type'));
            $destinationRecord->setValue('entity_type', $sourceRecord->getValue('entity_type'));
            $destinationRecord->setValue('request_path', $sourceRecord->getValue('request_path'));

            $targetPath = $sourceRecord->getValue('target_path');

            $productId = $sourceRecord->getValue('product_id');
            $categoryId = $sourceRecord->getValue('category_id');
            $cmsPageId = $sourceRecord->getValue('cms_page_id');
            if (!empty($productId) && !empty($categoryId)) {
                $destinationRecord->setValue('metadata', json_encode(['category_id' => $categoryId]));
                $destinationRecord->setValue('entity_type', 'product');
                $destinationRecord->setValue('entity_id', $productId);
                $targetPath = "catalog/product/view/id/$productId/category/$categoryId";
            } elseif (!empty($productId) && empty($categoryId)) {
                $destinationRecord->setValue('entity_type', 'product');
                $destinationRecord->setValue('entity_id', $productId);
                $targetPath = 'catalog/product/view/id/' . $productId;
            } elseif (empty($productId) && !empty($categoryId)) {
                $destinationRecord->setValue('entity_type', 'category');
                $destinationRecord->setValue('entity_id', $categoryId);
                if ($sourceRecord->getValue('entity_type') != 'custom') {
                    $targetPath = 'catalog/category/view/id/' . $categoryId;
                }
            } elseif (!empty($cmsPageId)) {
                $destinationRecord->setValue('entity_id', $cmsPageId);
            } else {
                $destinationRecord->setValue('entity_id', 0);
            }

            $normalizedRequestPath = strtolower($sourceRecord->getValue('request_path'));
            if (!empty($this->duplicateIndex[$normalizedRequestPath])) {
                $shouldResolve = false;

                foreach ($this->duplicateIndex[$normalizedRequestPath] as &$duplicate) {
                    $onStore = $duplicate['store_id'] == $sourceRecord->getValue('store_id');
                    if ($onStore && empty($duplicate['used'])) {
                        $duplicate['used'] = true;
                        break;
                    }
                    if ($onStore) {
                        $shouldResolve = true;
                    }
                }
                if ($shouldResolve) {
                    $hash = md5(mt_rand());
                    $requestPath = preg_replace(
                        '/^(.*)\.([^\.]+)$/i',
                        '$1-' . $hash . '.$2',
                        $sourceRecord->getValue('request_path'),
                        1,
                        $isChanged
                    );
                    if (!$isChanged) {
                        $requestPath = $sourceRecord->getValue('request_path') . '-' . $hash;
                    }
                    $this->resolvedDuplicates[$destinationRecord->getValue('entity_type')]
                    [$destinationRecord->getValue('entity_id')]
                    [$sourceRecord->getValue('store_id')] = $hash;
                    $destinationRecord->setValue('request_path', $requestPath);
                    $this->resultMessages[] = 'Duplicate resolved. '
                        . sprintf(
                            'Request path was: %s Target path was: %s Store ID: %s New request path: %s',
                            $sourceRecord->getValue('request_path'),
                            $sourceRecord->getValue('target_path'),
                            $sourceRecord->getValue('store_id'),
                            $destinationRecord->getValue('request_path')
                        );
                }
            }

            $destinationRecord->setValue(
                'target_path',
                $targetPath
            );
            $destination->addRecord($destinationRecord);
        }
    }

    /**
     * Copy eav data
     *
     * @param string $sourceName
     * @param string $destinationName
     * @param string $type
     * @return void
     */
    protected function copyEavData($sourceName, $destinationName, $type)
    {
        $destinationDocument = $this->destination->getDocument($destinationName);
        $pageNumber = 0;
        while (!empty($recordsData = $this->source->getRecords($sourceName, $pageNumber))) {
            $pageNumber++;
            $records = $destinationDocument->getRecords();
            foreach ($recordsData as $row) {
                $this->progress->advance();
                $row['value_id'] = null;
                unset($row['entity_type_id']);
                if (!empty($this->resolvedDuplicates[$type][$row['entity_id']][$row['store_id']])) {
                    $row['value'] = $row['value'] . '-'
                        . $this->resolvedDuplicates[$type][$row['entity_id']][$row['store_id']];
                } elseif (!empty($this->resolvedDuplicates[$type][$row['entity_id']]) && $row['store_id'] == 0) {
                    foreach ($this->resolvedDuplicates[$type][$row['entity_id']] as $storeId => $urlKey) {
                        $storeRow = $row;
                        $storeRow['store_id'] = $storeId;
                        $storeRow['value'] = $storeRow['value'] . '-' . $urlKey;
                        $storeRow = $this->helper->processFields(
                            MapInterface::TYPE_DEST,
                            $destinationName,
                            $storeRow,
                            true
                        );
                        $records->addRecord($this->recordFactory->create(['data' => $storeRow]));
                        if (!isset($this->resolvedDuplicates[$destinationName])) {
                            $this->resolvedDuplicates[$destinationName] = 0;
                        }
                        $this->resolvedDuplicates[$destinationName]++;
                    }
                }
                $row = $this->helper->processFields(MapInterface::TYPE_DEST, $destinationName, $row, true);
                $records->addRecord($this->recordFactory->create(['data' => $row]));
            }
            $this->destination->saveRecords($destinationName, $records, true);
        }
    }

    /**
     * Get iterations count for step
     *
     * @return int
     */
    protected function getIterationsCount()
    {
        return $this->source->getRecordsCount($this->getName())
            + $this->source->getRecordsCount('catalog_category_entity_url_key')
            + $this->source->getRecordsCount('catalog_product_entity_url_key');
    }

    /**
     * Get duplicates list
     *
     * @return array
     */
    public function getDuplicatesList()
    {
        $subSelect = $this->getRewritesSelect();
        $subSelect->group(['request_path', 'store_id'])
            ->having('COUNT(*) > 1');

        /** @var \Migration\ResourceModel\Adapter\Mysql $adapter */
        $adapter = $this->source->getAdapter();

        /** @var \Magento\Framework\DB\Select $select */
        $select = $adapter->getSelect();
        $select->from(['t' => $this->source->addDocumentPrefix($this->getName())], ['t.*'])
            ->join(
                ['t2' => new \Zend_Db_Expr(sprintf('(%s)', $subSelect->assemble()))],
                't2.request_path = t.request_path AND t2.store_id = t.store_id',
                []
            )
            ->order(['store_id', 'request_path', 'priority']);
        $resultData = $adapter->loadDataFromSelect($select);

        return $resultData;
    }

    /**
     * Initialize temporary table and insert UrlRewrite data
     *
     * @param ProductRewritesWithoutCategoriesInterface $productRewritesWithoutCategories
     * @param ProductRewritesIncludedIntoCategoriesInterface $productRewritesIncludedIntoCategories
     * @param CategoryRewritesInterface $categoryRewrites
     * @param CmsPageRewritesInterface $cmsPageRewrites
     * @param RedirectsRewritesInterface $redirectsRewrites
     * @codeCoverageIgnore
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return void
     */
    public function initTemporaryTable(
        ProductRewritesWithoutCategoriesInterface $productRewritesWithoutCategories,
        ProductRewritesIncludedIntoCategoriesInterface $productRewritesIncludedIntoCategories,
        CategoryRewritesInterface $categoryRewrites,
        CmsPageRewritesInterface $cmsPageRewrites,
        RedirectsRewritesInterface $redirectsRewrites
    ){
        if (self::$dataInitialized) {
            return;
        }
        $this->productRewritesWithoutCategories = $productRewritesWithoutCategories;
        $this->productRewritesIncludedIntoCategories = $productRewritesIncludedIntoCategories;
        $this->categoryRewrites = $categoryRewrites;
        $this->cmsPageRewrites = $cmsPageRewrites;
        $this->redirectsRewrites = $redirectsRewrites;
        /** @var \Migration\ResourceModel\Adapter\Mysql $adapter */
        $adapter = $this->source->getAdapter();
        $this->create();
        $this->collectProductRewrites($adapter);
        $this->categoryRewrites->collectRewrites();
        $this->redirectsRewrites->collectRewrites();
        $this->redirectsRewrites->removeDuplicatedUrlRedirects();
        $this->redirectsRewrites->collectRedirects();
        $this->cmsPageRewrites->collectRewrites();
        self::$dataInitialized = true;
    }

    /**
     * Fulfill temporary table with product url rewrites
     *
     * @param \Migration\ResourceModel\Adapter\Mysql $adapter
     * @return void
     */
    protected function collectProductRewrites(\Migration\ResourceModel\Adapter\Mysql $adapter)
    {
        $queryExecute = function ($queries) use ($adapter) {
            foreach ($queries as $query) {
                $adapter->getSelect()->getAdapter()->query($query);
            }
        };
        $queryExecute($this->productRewritesWithoutCategories->getQueryProductsSavedForDefaultScope());
        $queryExecute($this->productRewritesWithoutCategories->getQueryProductsSavedForParticularStoreView());
        $queryExecute($this->productRewritesIncludedIntoCategories->getQueryProductsSavedForDefaultScope());
        $queryExecute($this->productRewritesIncludedIntoCategories->getQueryProductsSavedForParticularStoreView());
    }
}
