<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\VisualMerchandiser;

use Migration\ResourceModel\Record;
use Migration\Handler\AbstractHandler;

/**
 * Class InsertMode
 */
class InsertMode extends AbstractHandler
{
    /**
     * @inheritdoc
     */
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
