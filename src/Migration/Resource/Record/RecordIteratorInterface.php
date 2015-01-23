<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Resource\Record;

interface RecordIteratorInterface extends \SeekableIterator, \Countable
{
    /**
     * @param ProviderInterface $recordProvider
     * @return $this
     */
    public function setRecordProvider($recordProvider);

    /**
     * @inheritdoc
     * @return array
     */
    public function current();
}
