<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Eav\Integrity;

use Migration\Step\Eav\Helper;
use Migration\ResourceModel\Source;
use Migration\Step\Eav\Model\IgnoredAttributes;
use Migration\Reader\ClassMap as ClassMapReader;

/**
 * Class ClassMap
 */
class ClassMap
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var array
     */
    private $tableKeys;

    /**
     * @var Source
     */
    private $source;

    /**
     * @var ClassMapReader
     */
    private $classMapReader;

    /**
     * @var IgnoredAttributes
     */
    private $ignoredAttributes;

    /**
     * @var array
     */
    private $classMapFields = [
        'eav_attribute' => [
            'attribute_model',
            'backend_model',
            'frontend_model',
            'source_model',
        ],
        'catalog_eav_attribute' => [
            'frontend_input_renderer',
        ],
        'customer_eav_attribute' => [
            'data_model',
        ],
        'eav_entity_type' => [
            'entity_model',
            'attribute_model',
            'increment_model',
            'entity_attribute_collection',
        ]
    ];

    /**
     * @param Helper $helper
     * @param Source $source
     * @param ClassMapReader $classMapReader
     * @param IgnoredAttributes $ignoredAttributes
     */
    public function __construct(
        Helper $helper,
        Source $source,
        ClassMapReader $classMapReader,
        IgnoredAttributes $ignoredAttributes
    ) {
        $this->helper = $helper;
        $this->source = $source;
        $this->classMapReader = $classMapReader;
        $this->ignoredAttributes = $ignoredAttributes;
    }

    /**
     * Check Class Mapping
     *
     * @return array
     */
    public function checkClassMapping()
    {
        $classMapFailed = [];
        foreach ($this->classMapFields as $tableName => $classMapFields) {
            $sourceRecords = $this->helper->getSourceRecords($tableName);
            $sourceRecords = $this->ignoredAttributes->clearIgnoredAttributes($sourceRecords);
            $primaryKeyName = $this->getPrimaryKeyName($tableName);
            foreach ($sourceRecords as $attribute) {
                foreach ($classMapFields as $field) {
                    $className = $attribute[$field];
                    if (empty($className)) {
                        continue;
                    }
                    if (!$this->classMapReader->hasMap($className)) {
                        $classMapFailed[] = [
                            'document' => $tableName,
                            'field' => $field,
                            'error' => sprintf(
                                'Class %s is not mapped in record %s=%s',
                                $attribute[$field],
                                $primaryKeyName,
                                $attribute[$primaryKeyName]
                            )
                        ];
                    }
                }
            }
        }
        return $classMapFailed;
    }

    /**
     * Get primary key name
     *
     * @param string $documentName
     * @return mixed
     */
    private function getPrimaryKeyName($documentName)
    {
        if (isset($this->tableKeys[$documentName])) {
            return $this->tableKeys[$documentName];
        }
        $this->tableKeys[$documentName] = null;
        $sourceFields = $this->source->getDocument($documentName)->getStructure()->getFields();
        foreach ($sourceFields as $params) {
            if ($params['PRIMARY']) {
                $this->tableKeys[$documentName] = $params['COLUMN_NAME'];
                break;
            }
        }
        return $this->tableKeys[$documentName];
    }
}
