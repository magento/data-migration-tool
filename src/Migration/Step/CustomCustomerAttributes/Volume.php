<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\CustomCustomerAttributes;

use Migration\Step\CustomCustomerAttributes;

/**
 * Class Volume
 */
class Volume extends CustomCustomerAttributes
{
    /**
     * Volume check
     *
     * @return bool
     */
    public function perform()
    {
        $result = true;
        $this->progress->start(count($this->getDocumentList()));
        foreach ($this->getDocumentList() as $sourceName => $destinationName) {
            $this->progress->advance();
            $sourceFields = $this->source->getDocument($sourceName)->getStructure()->getFields();
            $destinationFields = $this->destination->getDocument($destinationName)->getStructure()->getFields();
            $result &= empty(array_diff_key($sourceFields, $destinationFields));
            $result &= $this->source->getRecordsCount($sourceName) ==
                $this->destination->getRecordsCount($destinationName);
        }
        $this->progress->finish();
        return (bool)$result;
    }
}
