<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\SerializeToJson;

use Migration\Logger\Logger;
use Migration\ResourceModel\Record;
use Migration\Exception;
use Migration\Handler\AbstractHandler;

/**
 * Handler to transform field according to the map for special tables with additional logic
 */
class ConvertWithConditions extends AbstractHandler
{
    /**
     * Property which checks for additional statement during processing main field
     *
     * @var string
     */
    protected $conditionalField;

    /**
     * Pattern for values from additional field which should be processed with default unserialize flow
     *
     * @var string
     */
    protected $conditionalFieldValuesPattern;

    /**
     * Sometimes fields has a broken serialize data, for example enterprise_logging_event_changes.result_data.
     * If property sets to true, ignore all notices from unserialize()
     *
     * @var bool
     *
     */
    protected $ignoreBrokenData;

    /**
     * @param string $conditionalField
     * @param string $conditionalFieldValuesPattern
     * @param bool $ignoreBrokenData
     */
    public function __construct($conditionalField, $conditionalFieldValuesPattern, $ignoreBrokenData = false)
    {
        $this->conditionalField = $conditionalField;
        $this->conditionalFieldValuesPattern = $conditionalFieldValuesPattern;
        $this->ignoreBrokenData = $ignoreBrokenData;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        $value = $recordToHandle->getValue($this->field);
        if (null !== $value) {
            if ($this->shouldProcessField($recordToHandle->getData()[$this->conditionalField])) {
                $unserializeData = $this->ignoreBrokenData ? @unserialize($value) : unserialize($value);
                $value = false === $unserializeData ? json_encode([]) : json_encode($unserializeData);
            }
        }
        $recordToHandle->setValue($this->field, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function validate(Record $record)
    {
        parent::validate($record);
        if ($this->conditionalField && !isset($record->getData()[$this->conditionalField])) {
            throw new Exception("Conditional field {$this->conditionalField} not found in the record.");
        }
    }

    /**
     * @param string $valueOfConditionalField
     * @return bool
     */
    protected function shouldProcessField($valueOfConditionalField)
    {
        preg_match($this->conditionalFieldValuesPattern, $valueOfConditionalField, $matches);
        return (bool)$matches;
    }
}
