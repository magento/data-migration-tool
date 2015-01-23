<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Resource\Document;

interface ProviderInterface
{
    /**
     * Get Documents list
     *
     * @return array
     */
    public function getDocumentList();

    /**
     * Get Document Fields list
     * Returns array:
     * [
     *     'fieldName' => 'options'
     * ]
     *
     * @param string $documentName
     * @return array
     */
    public function getDocumentStructure($documentName);
}
