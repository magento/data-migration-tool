<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Resource\Document;

/**
 * Document iterator interface class
 */
interface DocumentIteratorInterface extends \SeekableIterator, \Countable
{
    /**
     * Set document provider
     *
     * @param ProviderInterface $documentProvider
     * @return $this
     */
    public function setDocumentProvider($documentProvider);

    /**
     * @inheritdoc
     * @return DocumentInterface
     */
    public function current();
}
