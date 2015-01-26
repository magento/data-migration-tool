<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Resource\Record;

/**
 * Record iterator interface class
 */
interface RecordIteratorInterface extends \SeekableIterator, \Countable
{
    /**
     * Set record provider
     *
     * @param ProviderInterface $recordProvider
     * @return $this
     */
    public function setRecordProvider($recordProvider);

    /**
     * @inheritdoc
     * @return array
     */
    public function current();

    /**
     * Get page size
     *
     * @return int
     */
    public function getPageSize();

    /**
     * Set page size
     *
     * @param int $pageSize
     * @return $this
     */
    public function setPageSize($pageSize);
}
