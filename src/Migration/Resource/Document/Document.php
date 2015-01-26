<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Resource\Document;

class Document implements DocumentInterface
{
    /**
     * @var ProviderInterface
     */
    protected $documentProvider;

    /**
     * @var \Migration\Resource\Record\RecordIteratorFactory
     */
    protected $recordIteratorFactory;

    /**
     * @var string
     */
    protected $documentName;

    /**
     * @param \Migration\Resource\Record\RecordIteratorFactory $recordIteratorFactory
     * @param string $documentName
     */
    public function __construct(
        \Migration\Resource\Record\RecordIteratorFactory $recordIteratorFactory,
        $documentName
    ) {
        $this->recordIteratorFactory = $recordIteratorFactory;
        $this->documentName = $documentName;
    }

    /**
     * @inheritdoc
     */
    public function getRecordIterator()
    {
        return $this->recordIteratorFactory->create(array(
            'documentName' => $this->getName(),
        ));
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->documentName;
    }
}
