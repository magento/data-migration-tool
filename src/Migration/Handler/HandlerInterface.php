<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

use Migration\ResourceModel\Record;

interface HandlerInterface
{
    /**
     * @param Record $recordToHandle
     * @param Record $oppositeRecord
     * @return mixed
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord);

    /**
     * Setting field, which should be processed in the handler
     *
     * @param string $field
     * @return $this
     */
    public function setField($field);
}
