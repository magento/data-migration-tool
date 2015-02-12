<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

use Migration\Resource\Record;

interface HandlerInterface
{
    /**
     * Handling record
     *
     * @param Record $record
     * @param string $fieldName
     * @return void
     */
    public function handle(Record $record, $fieldName);
}
