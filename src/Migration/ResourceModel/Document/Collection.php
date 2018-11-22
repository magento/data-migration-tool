<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\ResourceModel\Document;

/**
 * Document iterator class
 */
class Collection extends \Migration\ResourceModel\AbstractCollection
{
    /**
     * @var \Migration\ResourceModel\Document[]
     */
    protected $data;

    /**
     * Get Document from collection
     *
     * @param string $documentName
     * @return \Migration\ResourceModel\Document|null
     */
    public function getDocument($documentName)
    {
        foreach ($this->data as $document) {
            if ($document->getName() == $documentName) {
                return $document;
            }
        }
        return null;
    }

    /**
     * Add Document to collection
     *
     * @param \Migration\ResourceModel\Document $document
     * @return $this
     */
    public function addDocument($document)
    {
        $this->data[] = $document;
        return $this;
    }
}
