<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Resource\Record;

/**
 * Convert handler interface class
 */
interface ConvertHandlerInterface extends \Iterator
{
    /**
     * Convert record
     *
     * @param mixed $data data
     * @param mixed $dataRaw data just after reading from document
     * @return mixed
     */
    public function convert($data, $dataRaw);
}
