<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

use Migration\ResourceModel\Record;

/**
 * Handler to transform field according to the map
 */
class ConvertDateFormat extends AbstractHandler implements HandlerInterface
{
    /**
     * @inheritdoc
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $replacePairs = [
            '<date_format>full</date_format>' => '<date_format>' . \IntlDateFormatter::FULL . '</date_format>',
            '<date_format>long</date_format>' => '<date_format>' . \IntlDateFormatter::LONG . '</date_format>',
            '<date_format>medium</date_format>' => '<date_format>' . \IntlDateFormatter::MEDIUM . '</date_format>',
            '<date_format>short</date_format>' => '<date_format>' . \IntlDateFormatter::SHORT . '</date_format>'
        ];
        $this->validate($recordToHandle);
        $value = $recordToHandle->getValue($this->field);
        $newValue = strtr($value, $replacePairs);
        $recordToHandle->setValue($this->field, $newValue);
    }
}
