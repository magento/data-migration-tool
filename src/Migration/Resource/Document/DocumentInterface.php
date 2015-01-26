<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Resource\Document;

/**
 * Document interface class
 */
interface DocumentInterface
{
    /**
     * Get record iterator
     *
     * @return \Migration\Resource\Record\RecordIteratorInterface
     */
    public function getRecordIterator();

    /**
     * Get Document name
     *
     * @return string
     */
    public function getName();
}
