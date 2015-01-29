<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Resource\Document;

/**
 * Document class
 */
class Document
{
    /**
     * @var \Migration\Resource\Record\RecordCollectionFactory
     */
    protected $recordCollectionFactory;

    /**
     * @var string
     */
    protected $structure;

    /**
     * @var string
     */
    protected $documentName;

    /**
     * @param \Migration\Resource\Record\RecordCollectionFactory $recordCollectionFactory
     * @param string $documentName
     */
    public function __construct(
        \Migration\Resource\Record\RecordCollectionFactory $recordCollectionFactory,
        \Migration\Resource\Document\Structure $structure,
        $documentName
    ) {
        $this->recordCollectionFactory = $recordCollectionFactory;
        $this->structure = $structure;
        $this->documentName = $documentName;
    }

    /**
     * @inheritdoc
     */
    public function getRecords()
    {
        return $this->recordCollectionFactory->create(array(
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

    /**
     * Get Document name
     *
     * @return string
     */
    public function getStructure()
    {
        return $this->structure;
    }
}
