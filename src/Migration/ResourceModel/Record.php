<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * Get structure
     *
     * @return Structure
     */
    public function getStructure()
    {
        return $this->structure;
    }

    /**
     * Set structure
     *
     * @param Structure $structure
     * @return void
     */
    public function setStructure($structure)
    {
        $this->structure = $structure;
    }

    /**
     * Set document
     *
     * @param Document $document
     * @return void
     */
    public function setDocument(Document $document)
    {
        $this->document = $document;
    }

    /**
     * Get document
     *
     * @return Document
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Validate structure
     *
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
     * Get column default value
     *
     * @param string $columnName
     * @return mixed
     * @throws Exception
     */
    public function getValueDefault($columnName)
    {
        if ($this->structure && !$this->structure->hasField($columnName)) {
            throw new Exception(
                "Record structure does not contain field $columnName on {$this->getDocument()->getName()}"
            );
        }
        $fields = $this->structure->getFields();
        if ($fields[$columnName]['DEFAULT'] === null && $fields[$columnName]['NULLABLE'] === false) {
            return '';
        }
        return $fields[$columnName]['DEFAULT'];
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
            throw new Exception(
                "Record structure does not contain field $columnName on {$this->getDocument()->getName()}"
            );
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
     * Get record default data
     *
     * @return array
     */
    public function getDataDefault()
    {
        $fields = [];
        foreach ($this->structure->getFields() as $code => $structure) {
            if ($structure['DEFAULT'] === null && $structure['NULLABLE'] === false) {
                $fields[$code] = '';
            } else {
                $fields[$code] = $structure['DEFAULT'];
            }
        }
        return $fields;
    }

    /**
     * Get fields
     *
     * @return array
     * @throws Exception
     */
    public function getFields()
    {
        if (empty($this->getStructure())) {
            throw new Exception("Structure not set");
        }

        return array_keys($this->structure->getFields());
    }
}
