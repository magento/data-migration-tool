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
    public function current(): mixed
    {
        return $this->data[$this->position];
    }

    /**
     * @inheritdoc
     */
    public function key(): mixed
    {
        return $this->position;
    }

    /**
     * @inheritdoc
     */
    public function next(): void
    {
        $this->position++;
    }

    /**
     * @inheritdoc
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * @inheritdoc
     */
    public function valid(): bool
    {
        return $this->key() < count($this->data);
    }

    /**
     * @inheritdoc
     */
    public function count(): int
    {
        return count($this->data);
    }
}
