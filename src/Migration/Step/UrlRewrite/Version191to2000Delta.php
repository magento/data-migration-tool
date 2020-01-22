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
use Migration\Step\UrlRewrite\Model\Version191to2000\Transformer;
use Migration\Step\UrlRewrite\Model\Version191to2000\CmsPageRewrites;

/**
 * Class Delta
 */
class Version191to2000Delta extends AbstractDelta
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
     * @var false|ResourceModel\Document
     */
    private $destProductCategory;

    /**
     * @var CmsPageRewrites
     */
    private $cmsPageRewrites;

    /**
     * @var bool
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
     * @param CmsPageRewrites $cmsPageRewrites
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
        CmsPageRewrites $cmsPageRewrites
    ) {
        $this->transformer = $transformer;
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
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    protected function getDocumentMap($document, $type)
    {
        return Version191to2000::DESTINATION;
    }

    /**
     * @inheritdoc
     */
    protected function transformdata($data, $sourceDocument, $destDocument, $recordTransformer, $destinationRecords)
    {
        $record = $this->recordFactory->create(['document' => $sourceDocument, 'data' => $data]);
        $destRecord = $this->recordFactory->create(['document' => $destDocument]);
        $this->transformer->transform($record, $destRecord);
        $destinationRecords->addRecord($destRecord);
        $this->saveProductCategoryRecord($record);
    }

    /**
     * Save Cms Page Rewrites
     */
    private function saveCmsPageRewrites()
    {
        /** @var \Magento\Framework\DB\Adapter\Pdo\Mysql $adapter */
        $adapter = $this->destination->getAdapter()->getSelect()->getAdapter();
        $adapter->delete(
            $this->destination->addDocumentPrefix(Version191to2000::DESTINATION),
            "entity_type = 'cms-page'"
        );
        $select = $this->cmsPageRewrites->getSelect();
        $urlRewrites = $this->source->getAdapter()->loadDataFromSelect($select);
        $this->destination->saveRecords(
            $this->source->addDocumentPrefix(Version191to2000::DESTINATION),
            $urlRewrites,
            true
        );
    }

    /**
     * Save Product Category Record
     *
     * @param \Migration\ResourceModel\Record $record
     */
    private function saveProductCategoryRecord($record)
    {
        if ($record->getValue('is_system')
            && $record->getValue('product_id')
            && $record->getValue('category_id')
            && $record->getValue('request_path') !== null
        ) {
            /** @var \Magento\Framework\DB\Select $select */
            $select = $this->destination->getAdapter()->getSelect();
            $select->from($this->destination->addDocumentPrefix(Version191to2000::DESTINATION_PRODUCT_CATEGORY))
                ->where('url_rewrite_id = ?', $record->getValue('url_rewrite_id'))
                ->where('category_id = ?', $record->getValue('category_id'))
                ->where('product_id = ?', $record->getValue('product_id'));
            if (!$this->destination->getAdapter()->loadDataFromSelect($select)) {
                $this->destination->saveRecords(
                    $this->destination->addDocumentPrefix(Version191to2000::DESTINATION_PRODUCT_CATEGORY),
                    [[
                        'url_rewrite_id' => $record->getValue('url_rewrite_id'),
                        'category_id' => $record->getValue('category_id'),
                        'product_id' => $record->getValue('product_id')
                    ]],
                    true
                );
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function markRecordsProcessed($documentName, $idKeys, $items)
    {
        $this->urlRewritesChangedFlag = true;
        parent::markRecordsProcessed($documentName, $idKeys, $items);
    }
}
