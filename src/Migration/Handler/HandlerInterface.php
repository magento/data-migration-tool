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
     * @return Record
     */
    public function handle(Record $record);

    /**
     * Setting field, which should be processed in the handler
     *
     * @param string $field
     * @return $this
     */
    public function setField($field);
}
