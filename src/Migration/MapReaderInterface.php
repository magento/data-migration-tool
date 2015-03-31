<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration;

/**
 * Interface MapReaderInterface
 */
interface MapReaderInterface
{
    const TYPE_SOURCE = 'source';
    const TYPE_DEST = 'destination';

    /**
     * @param string $document
     * @param string $field
     * @param string $type
     * @return mixed
     */
    public function isFieldIgnored($document, $field, $type);

    /**
     * @param string $document
     * @param string $type
     * @return mixed
     */
    public function isDocumentIgnored($document, $type);

    /**
     * @param string $document
     * @param string $type
     * @return mixed
     */
    public function isDocumentMaped($document, $type);

    /**
     * @param string $document
     * @param string $field
     * @param string $type
     * @return mixed
     */
    public function isFieldMapped($document, $field, $type);

    /**
     * @param string $document
     * @param string $type
     * @return mixed
     */
    public function getDocumentMap($document, $type);

    /**
     * @param string $document
     * @param string $field
     * @param string $type
     * @return mixed
     */
    public function getFieldMap($document, $field, $type);

    /**
     * @param string $document
     * @param string $field
     * @param string $type
     * @return mixed
     */
    public function getHandlerConfig($document, $field, $type);

    /**
     * @param array $documents
     * @return array
     */
    public function getDeltaDocuments($documents);
}
