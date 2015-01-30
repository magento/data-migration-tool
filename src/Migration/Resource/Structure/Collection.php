<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Resource\Document;

/**
 * Document iterator class
 */
class StructureCollection extends \Migration\Resource\AbstractCollection
{
    /**
     * @var \Migration\Resource\Structure[]
     */
    protected $data;

    /**
     * Get Structure from collection
     *
     * @param $documentName
     * @return \Migration\Resource\Structure|null
     */
    public function getStructure($documentName)
    {
        if (isset($this->data[$documentName])) {
            return $this->data[$documentName];
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
        $this->data[$documentName] = $structure;
        return $this;
    }
}
