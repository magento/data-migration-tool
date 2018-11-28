<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\ResourceModel;

/**
 * Record iterator class
 */
abstract class AbstractCollection implements \Iterator, \Countable
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @var int
     */
    protected $position;

    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
        $this->rewind();
    }

    /**
     * @inheritdoc
     */
    public function current()
    {
        return $this->data[$this->position];
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
        $this->position++;
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * @inheritdoc
     */
    public function valid()
    {
        return $this->key() < count($this->data);
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return count($this->data);
    }
}
