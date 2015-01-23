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
     * @param ProviderInterface $documentProvider
     * @param \Migration\Resource\Record\RecordIteratorFactory $recordsIteratorFactory
     * @param string $documentName
     */
    public function __construct(
        ProviderInterface $documentProvider,
        \Migration\Resource\Record\RecordIteratorFactory $recordsIteratorFactory,
        $documentName
    ) {
        $this->documentProvider = $documentProvider;
        $this->recordIteratorFactory = $recordsIteratorFactory;
        $this->documentName = $documentName;
    }

    /**
     * @inheritdoc
     */
    public function getRecordsIterator()
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
