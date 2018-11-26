<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\Settings;

use Migration\ResourceModel\Record;
use Migration\Handler\AbstractHandler;

/**
 * Handler to convert name of template
 */
class Template extends AbstractHandler
{
    /**
     * @inheritdoc
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        $value = $recordToHandle->getValue($this->field);
        $valueOpposite = $oppositeRecord->getValue($this->field);
        if (!is_numeric($value) && $valueOpposite) {
            $value = $valueOpposite;
        }
        $recordToHandle->setValue($this->field, $value);
    }
}
