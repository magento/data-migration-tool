<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Integrity;

use Migration\Logger\Logger;
use Migration\MapReader;
use Migration\Resource;

class Map extends AbstractIntegrity
{
    /**
     * {@inheritdoc}
     */
    public function perform()
    {
        $this->check($this->source->getDocumentList(), MapReader::TYPE_SOURCE);
        $this->check($this->destination->getDocumentList(), MapReader::TYPE_DEST);
        return $this->checkForErrors();
    }
}
