<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

use Migration\ResourceModel\Record;

/**
 * Handler to create store group code from its name
 */
class StoreGroupCode extends AbstractHandler
{
    /**
     * @var string
     */
    private $storeGroupNameField = 'name';

    /**
     * {@inheritdoc}
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        $groupName = $recordToHandle->getValue($this->storeGroupNameField);
        $code = preg_replace('/\s+/', '_', $groupName);
        $code = preg_replace('/[^a-z0-9-_]/', '', strtolower($code));
        $code = preg_replace('/^[^a-z]+/', '', $code);
        if (empty($code)) {
            $code = 'store_group';
        }
        $recordToHandle->setValue($this->field, $code);
    }
}
