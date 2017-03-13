<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\UrlRewrite;

use Migration\Reader\MapInterface;
use Migration\Config;

/**
 * Class Helper
 */
class Helper
{
    /**
     * @var string
     */
    protected $editionMigrate = '';

    /**
     * Config data of staging module
     *
     * @var array
     */
    private $stagingConfig = [
        'tables' => ['catalog_category_entity_varchar', 'catalog_product_entity_varchar'],
        'field_entity_id' => 'entity_id',
        'field_staging' => 'row_id',
    ];

    /**
     * @param Config $config
     */
    public function __construct(
        \Migration\Config $config
    ) {
        $this->editionMigrate = $config->getOption('edition_migrate');
    }

    /**
     * Fields processor
     *
     * @param string $resourceType
     * @param string $tableName
     * @param array $fields
     * @param bool $inKeys
     * @return array
     */
    public function processFields($resourceType, $tableName, array $fields, $inKeys = false)
    {
        return $this->processFieldsOfStagingModule($resourceType, $tableName, $fields, $inKeys);
    }

    /**
     * Rename fields of staging module
     *
     * @param string $resourceType
     * @param string $tableName
     * @param array $fields
     * @param bool $inKeys
     * @return array
     */
    private function processFieldsOfStagingModule($resourceType, $tableName, array $fields, $inKeys = false)
    {
        $fieldEntityId = $this->stagingConfig['field_entity_id'];
        $fieldStaging = $this->stagingConfig['field_staging'];
        $tablesStaging = $this->stagingConfig['tables'];

        if (empty($this->editionMigrate)
            || $this->editionMigrate == Config::EDITION_MIGRATE_CE_TO_CE
            || $resourceType == MapInterface::TYPE_SOURCE
            || !in_array($tableName, $tablesStaging)
        ) {
            return $fields;
        }
        if ($inKeys && isset($fields[$fieldEntityId])) {
            $fields[$fieldStaging] = $fields[$fieldEntityId];
            unset($fields[$fieldEntityId]);
        } else {
            $map = function ($item) use ($fieldEntityId, $fieldStaging) {
                return $item == $fieldEntityId ? $fieldStaging : $item;
            };
            $fields = array_map($map, $fields);
        }
        return $fields;
    }
}
