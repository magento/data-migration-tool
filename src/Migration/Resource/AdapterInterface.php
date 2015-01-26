<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Resource;

interface AdapterInterface
    extends \Migration\Resource\Document\ProviderInterface, \Migration\Resource\Record\ProviderInterface
{
    /**
     * @param string $documentName
     * @param array $records
     * @return int
     */
    public function insertRecords($documentName, $records);
}
