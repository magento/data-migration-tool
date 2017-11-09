<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Model;

use \Migration\ResourceModel\Document;

/**
 * Class DocumentIdField
 */
class DocumentIdField
{
    /**
     * @param Document $document
     * @return string|null
     */
    public function getFiled(Document $document)
    {
        $fields = $document->getStructure()->getFields();
        foreach ($fields as $params) {
            if ($params['PRIMARY'] && $params['IDENTITY']) {
                return $params['COLUMN_NAME'];
            }
        }
        foreach ($fields as $params) {
            if ($params['PRIMARY']) {
                return $params['COLUMN_NAME'];
            }
        }
        return null;
    }
}
