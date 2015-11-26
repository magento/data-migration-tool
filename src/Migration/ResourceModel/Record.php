<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\ResourceModel;

use Migration\Exception;

/**
 * Record iterator class
 */
class Record
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @var \Migration\ResourceModel\Structure
     */
    protected $structure;

    /**
     * @var \Migration\ResourceModel\Document;
     */
    protected $document;

    /**
     * @param array $data
     * @param Document $document
     */
    public function __construct(array $data = [], Document $document = null)
    {
        $this->data = $data;
        if ($document !== null) {
            $this->setStructure($document->getStructure());
            $this->setDocument($document);
        }
    }

    /**
     * @return Structure
     */
    public function getStructure()
    {
        return $this->structure;
    }

    /**
     * @param Structure $structure
     * @return void
     */
    public function setStructure($structure)
    {
        $this->structure = $structure;
    }

    /**
     * @param Document $document
     * @return void
     */
    public function setDocument(Document $document)
    {
        $this->document = $document;
    }

    /**
     * @return Document
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @param Structure $structure
     * @return bool
     */
    public function validateStructure($structure = null)
    {
        if (!$structure) {
            $structure = $this->structure;
        }
        if (!$structure) {
            return false;
        }
        if (!$structure->getFields()) {
            return true;
        }

        return count(array_diff_key($this->data, $structure->getFields())) == 0;
    }

    /**
     * Get column value
     *
     * @param string $columnName
     * @return mixed
     */
    public function getValue($columnName)
    {
        return isset($this->data[$columnName]) ? $this->data[$columnName] : null;
    }

    /**
     * Set column value
     *
     * @param string $columnName
     * @param mixed $value
     * @return $this
     * @throws Exception
     */
    public function setValue($columnName, $value)
    {
        if ($this->structure && !$this->structure->hasField($columnName)) {
            throw new Exception("Record structure does not contain field $columnName");
        }
        $this->data[$columnName] = $value;
        return $this;
    }

    /**
     * Set record data
     *
     * @param array $data
     * @return $this
     * @throws Exception
     */
    public function setData($data)
    {
        $this->data = $data;
        if ($this->structure && !$this->validateStructure()) {
            throw new Exception("Record structure does not match provided Data");
        }
        return $this;
    }

    /**
     * Get record data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getFields()
    {
        if (empty($this->structure)) {
            throw new Exception("Structure not set");
        }

        return array_keys($this->structure->getFields());
    }
}
