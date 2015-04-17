<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\CustomCustomerAttributes;

use Migration\Step\CustomCustomerAttributes;

/**
 * Class Integrity
 */
class Integrity extends CustomCustomerAttributes
{

    /**
     * Integrity check
     *
     * @return bool
     */
    public function perform()
    {
        $result = true;
        $this->progress->start(count($this->getDocumentList()));
        foreach ($this->getDocumentList() as $sourceName => $destinationName) {
            $this->progress->advance();
            $result &= (bool)$this->source->getDocument($sourceName);
            $result &= (bool)$this->destination->getDocument($destinationName);
        }
        $this->progress->finish();
        return (bool)$result;
    }
}
