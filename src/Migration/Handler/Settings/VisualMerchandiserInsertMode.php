<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\Settings;

use Migration\ResourceModel\Record;
use Migration\Handler\AbstractHandler;

class VisualMerchandiserInsertMode extends AbstractHandler
{
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $valueMap = [
            '1' => '0',
            '2' => '1'
        ];
        $this->validate($recordToHandle);
        $value = $recordToHandle->getValue($this->field);
        $recordToHandle->setValue($this->field, $valueMap[$value]);
    }
}