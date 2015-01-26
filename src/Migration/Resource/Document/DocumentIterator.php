<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Resource\Document;

class DocumentIterator implements DocumentIteratorInterface
{
    /**
     * @var ProviderInterface
     */
    protected $documentProvider;

    /**
     * @var array
     */
    protected $items;

    /**
     * @var int
     */
    protected $position;

    /**
     * @var DocumentFactory
     */
    protected $documentFactory;

    /**
     * @var \Migration\Resource\Record\RecordIteratorInterface
     */
    protected $recordIterator;

    /**
     * @param DocumentFactory $documentFactory
     * @param \Migration\Resource\Record\RecordIteratorInterface $recordIterator
     */
    public function __construct(
        DocumentFactory $documentFactory,
        \Migration\Resource\Record\RecordIteratorInterface $recordIterator
    ) {
        $this->documentFactory = $documentFactory;
        $this->recordIterator = $recordIterator;
    }

    /**
     * @inheritdoc
     * @return DocumentInterface
     */
    public function current()
    {
        return $this->documentFactory->create([
            'documentProvider' => $this->documentProvider,
            'recordIterator' => $this->recordIterator,
            'documentName' => $this->items[$this->position]
        ]);
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
        $this->position++;
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * @inheritdoc
     */
    public function seek($position)
    {
        $this->position = $position;
    }

    /**
     * @inheritdoc
     */
    public function valid()
    {
        return $this->key() < count($this->items);
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * @param ProviderInterface $documentProvider
     * @return void
     */
    public function setDocumentProvider($documentProvider)
    {
        $this->documentProvider = $documentProvider;
        $this->items = $this->documentProvider->getDocumentList();
        $this->position = 0;
    }
}
