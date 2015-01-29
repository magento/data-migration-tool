<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Resource\Document;

/**
 * Document iterator class
 */
class DocumentCollection extends \Migration\Resource\AbstractCollection
{
    /**
     * @param string $documentName
     * @return null
     */
    public function getDocument($documentName)
    {
        if (isset($this->data[$documentName])) {
            return $this->data[$documentName];
        }
        return null;
    }

    /**
     * @param Document $document
     * @return $this
     */
    public function addDocument($document)
    {
        $this->data[$document->getName()] = $document;
        return $this;
    }
}
