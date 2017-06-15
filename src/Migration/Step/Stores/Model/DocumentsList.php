<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Stores\Model;

/**
 * Class DocumentsList returns list of source and destinations documents
 */
class DocumentsList
{
    /**
     * @return array
     */
    public function getSourceDocuments()
    {
        $map = $this->getDocumentsMap();
        return array_keys($map);
    }

    /**
     * @return array
     */
    public function getDestinationDocuments()
    {
        $map = $this->getDocumentsMap();
        return array_values($map);
    }

    /**
     * @return array
     */
    public function getDocumentsMap()
    {
        return [
            'core_store' => 'store',
            'core_store_group' => 'store_group',
            'core_website' => 'store_website'
        ];
    }
}
