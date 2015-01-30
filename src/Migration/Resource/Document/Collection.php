<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Resource\Document;

/**
 * Document iterator class
 */
class Collection extends \Migration\Resource\AbstractCollection
{
    /**
     * @var \Migration\Resource\Document[]
     */
    protected $data;

    /**
     * Get Document from collection
     *
     * @param $documentName
     * @return \Migration\Resource\Document|null
     */
    public function getDocument($documentName)
    {
        if (isset($this->data[$documentName])) {
            return $this->data[$documentName];
        }
        return null;
    }

    /**
     * Add Document to collection
     *
     * @param \Migration\Resource\Document $document
     * @return $this
     */
    public function addDocument($document)
    {
        $this->data[$document->getName()] = $document;
        return $this;
    }
}
