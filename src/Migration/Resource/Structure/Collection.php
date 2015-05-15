<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Resource\Structure;

/**
 * Document iterator class
 */
class Collection extends \Migration\Resource\AbstractCollection
{
    /**
     * @var array
     */
    protected $structureDocuments = [];

    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->structureDocuments = array_flip(array_keys($data));
        $this->data = array_values($data);
        $this->rewind();
    }

    /**
     * Get Structure from collection
     *
     * @param string $documentName
     * @return \Migration\Resource\Structure|null
     */
    public function getStructure($documentName)
    {
        if (isset($this->structureDocuments[$documentName])) {
            return $this->data[$this->structureDocuments[$documentName]];
        }
        return null;
    }

    /**
     * Add Structure to collection
     *
     * @param string $documentName
     * @param \Migration\Resource\Structure $structure
     * @return $this
     */
    public function addStructure($documentName, $structure)
    {
        $position = count($this->data);
        $this->data[] = $structure;
        $this->structureDocuments[$documentName] = $position;
        return $this;
    }
}
