<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\ResourceModel;

/**
 * Document class
 */
class Document
{
    /**
     * @var \Migration\ResourceModel\Record\CollectionFactory
     */
    protected $recordCollectionFactory;

    /**
     * @var \Migration\ResourceModel\Structure
     */
    protected $structure;

    /**
     * @var string
     */
    protected $documentName;

    /**
     * @param Record\CollectionFactory $recordCollectionFactory
     * @param Structure $structure
     * @param string $documentName
     */
    public function __construct(
        \Migration\ResourceModel\Record\CollectionFactory $recordCollectionFactory,
        \Migration\ResourceModel\Structure $structure,
        $documentName
    ) {
        $this->recordCollectionFactory = $recordCollectionFactory;
        $this->structure = $structure;
        $this->documentName = $documentName;
    }

    /**
     * Get records
     *
     * @return Record\Collection
     */
    public function getRecords()
    {
        return $this->recordCollectionFactory->create([
            'structure' => $this->structure,
            'documentName' => $this->getName(),
        ]);
    }

    /**
     * Get Document name
     *
     * @return string
     */
    public function getName()
    {
        return $this->documentName;
    }

    /**
     * Get document Structure
     *
     * @return \Migration\ResourceModel\Structure
     */
    public function getStructure()
    {
        return $this->structure;
    }
}
