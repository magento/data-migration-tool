<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * Is field ignored
     *
     * @param string $document
     * @param string $field
     * @param string $type
     * @return mixed
     */
    public function isFieldIgnored($document, $field, $type);

    /**
     * Is field data type ignored
     *
     * @param string $document
     * @param string $field
     * @param string $type
     * @return mixed
     */
    public function isFieldDataTypeIgnored($document, $field, $type);

    /**
     * Is document ignored
     *
     * @param string $document
     * @param string $type
     * @return mixed
     */
    public function isDocumentIgnored($document, $type);

    /**
     * Is document mapped
     *
     * @param string $document
     * @param string $type
     * @return mixed
     */
    public function isDocumentMapped($document, $type);

    /**
     * Is field mapped
     *
     * @param string $document
     * @param string $field
     * @param string $type
     * @return mixed
     */
    public function isFieldMapped($document, $field, $type);

    /**
     * Get document map
     *
     * @param string $document
     * @param string $type
     * @return mixed
     */
    public function getDocumentMap($document, $type);

    /**
     * Get field map
     *
     * @param string $document
     * @param string $field
     * @param string $type
     * @return mixed
     */
    public function getFieldMap($document, $field, $type);

    /**
     * Get handler configs
     *
     * @param string $document
     * @param string $field
     * @param string $type
     * @return mixed
     */
    public function getHandlerConfigs($document, $field, $type);
}
