<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Resource\Record;

class RecordIterator implements RecordIteratorInterface
{
    /**
     * @var array
     */
    protected $items;
    /**
     * @var int
     */
    protected $itemsCount;

    /**
     * @var int
     */
    protected $position;

    /**
     * @var int
     */
    protected $currentPage;

    /**
     * @var ProviderInterface
     */
    protected $recordProvider;

    /**
     * @var string
     */
    protected $documentName;

    /**
     * @param string $documentName
     */
    public function __construct($documentName) {
        $this->documentName = $documentName;
    }

    /**
     * @inheritdoc
     */
    public function current()
    {
        return $this->items[$this->position];
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        return $this->recordProvider->getPageSize() * $this->currentPage + $this->position;
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
        $this->position++;
        if ($this->position >= $this->recordProvider->getPageSize()) {
            $this->currentPage++;
            $this->loadPage();
        }
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        $this->currentPage = 0;
        $this->loadPage();
    }

    /**
     * @inheritdoc
     */
    public function seek($position)
    {
        $pageSize = $this->recordProvider->getPageSize();
        $this->currentPage =  (int)floor($position / $pageSize);
        $this->loadPage();
        $this->position =  ($position % $pageSize);
    }

    /**
     * @inheritdoc
     */
    public function valid()
    {
        return $this->key() < $this->itemsCount;
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return $this->itemsCount;
    }

    /**
     * @inheritdoc
     */
    public function setRecordProvider($recordProvider)
    {
        $this->recordProvider = $recordProvider;
        $this->itemsCount = $this->recordProvider->getRecordsCount();
    }

    /**
     * Load Page
     *
     * @return void
     */
    protected function loadPage()
    {
        $this->position = 0;
        $this->items = $this->recordProvider->loadPage($this->documentName, $this->currentPage);
    }
}
