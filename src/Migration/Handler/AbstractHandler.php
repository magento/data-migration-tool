<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

abstract class AbstractHandler
{
    /**
     * Field, processed by the handler
     *
     * @var string
     */
    protected $field;

    /**
     * Setting field, processed by the handler
     *
     * @param string $field
     * @return $this
     */
    public function setField($field)
    {
        $this->field = $field;
        return $this;
    }
}
