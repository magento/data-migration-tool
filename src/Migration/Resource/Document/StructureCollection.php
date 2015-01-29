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
     * @param string $documentName
     * @return null
     */
    public function getStructure($documentName)
    {
        if (isset($this->data[$documentName])) {
            return $this->data[$documentName];
        }
        return null;
    }

    /**
     * @param string $documentName
     * @param Structure $structure
     * @return $this
     */
    public function addStructure($documentName, $structure)
    {
        $this->data[$documentName] = $structure;
        return $this;
    }
}
