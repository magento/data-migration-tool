<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

use Migration\ResourceModel\Record;
use Migration\Exception;

/**
 * Handler to transform field according to the map
 */
class Convert extends AbstractHandler implements HandlerInterface
{
    /**
     * @var string
     */
    protected $map = [];

    /**
     * @var string|null
     */
    protected $defaultValue;

    /**
     * @param string $map
     * @param string|null $defaultValue
     * @throws Exception
     */
    public function __construct($map, $defaultValue = null)
    {
        $map = rtrim($map, ']');
        $map = ltrim($map, '[');
        $map = explode(';', $map);
        $resultMap = [];
        foreach ($map as $mapRecord) {
            $explodedRecord = explode(':', trim($mapRecord));
            if (count($explodedRecord) != 2) {
                throw new Exception('Invalid map provided to convert handler');
            }
            list($key, $value) = $explodedRecord;
            $resultMap[$key] = $value;
        }
        $this->map = $resultMap;
        if ($defaultValue) {
            $this->defaultValue = $defaultValue;
        }
    }

    /**
     * @inheritdoc
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        $value = $recordToHandle->getValue($this->field);
        if (isset($this->map[$value])) {
            $value = $this->map[$value];
        } elseif (null != $this->defaultValue) {
            $value = $this->defaultValue;
        }
        $recordToHandle->setValue($this->field, $value);
    }
}
