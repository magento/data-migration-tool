<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\Eav;

use Migration\Resource\Record;

/**
 * Class SetAttributeGroupCode
 */
class SetAttributeGroupCode extends \Migration\Handler\AbstractHandler implements \Migration\Handler\HandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        $recordToHandle->setValue(
            $this->field,
            preg_replace('/[^a-z0-9]+/', '-', strtolower($recordToHandle->getValue('attribute_group_name')))
        );
    }
}
