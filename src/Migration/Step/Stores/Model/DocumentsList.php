<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Stores\Model;

/**
 * Class DocumentsList returns list of source and destinations documents
 */
class DocumentsList
{
    /**
     * Get source documents
     *
     * @return array
     */
    public function getSourceDocuments()
    {
        $map = $this->getDocumentsMap();
        return array_keys($map);
    }

    /**
     * Get destination documents
     *
     * @return array
     */
    public function getDestinationDocuments()
    {
        $map = $this->getDocumentsMap();
        return array_values($map);
    }

    /**
     * Get documents map
     *
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
