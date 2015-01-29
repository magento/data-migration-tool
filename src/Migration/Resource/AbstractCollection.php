<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Resource;

/**
 * Record iterator class
 */
class AbstractCollection implements \Iterator, \Countable
{
    /**
     * @var []
     */
    protected $data;

    /**
     * @var int
     */
    protected $position;

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
