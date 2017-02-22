<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Stores;

/**
 * Class Helper
 */
class Helper
{
    /**
     * @return array
     */
    public function getDocumentList()
    {
        return [
            'core_store' => 'store',
            'core_store_group' => 'store_group',
            'core_website' => 'store_website'
        ];
    }
}
