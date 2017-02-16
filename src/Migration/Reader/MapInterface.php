<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Reader;

/**
 * Interface MapInterface
 */
interface MapInterface
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
     * @param string $field
     * @param string $type
     * @return mixed
     */
    public function isFieldDataTypeIgnored($document, $field, $type);

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
    public function isDocumentMapped($document, $type);

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
    public function getHandlerConfigs($document, $field, $type);
}
