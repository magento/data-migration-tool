<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Resource;

/**
 * Document class
 */
class Document
{
    /**
     * @var \Migration\Resource\Record\CollectionFactory
     */
    protected $recordCollectionFactory;

    /**
     * @var \Migration\Resource\Structure
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
        \Migration\Resource\Record\CollectionFactory $recordCollectionFactory,
        \Migration\Resource\Structure $structure,
        $documentName
    ) {
        $this->recordCollectionFactory = $recordCollectionFactory;
        $this->structure = $structure;
        $this->documentName = $documentName;
    }

    /**
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
     * @return \Migration\Resource\Structure
     */
    public function getStructure()
    {
        return $this->structure;
    }
}
