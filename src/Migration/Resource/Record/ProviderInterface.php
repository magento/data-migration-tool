<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Resource\Record;

interface ProviderInterface
{
    /**
     * Get Page size
     *
     * @return int
     */
    public function getPageSize();

    /**
     * Get Page size
     *
     * @return int
     */
    public function getRecordsCount();

    /**
     * Load Page
     *
     * @param $pageNumber
     * @return array
     */
    public function loadPage($pageNumber);
}
