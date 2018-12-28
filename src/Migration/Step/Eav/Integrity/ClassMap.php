<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Eav\Integrity;

use Migration\Step\Eav\Helper;
use Migration\ResourceModel\Destination;
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
     * @var Destination
     */
    private $destination;

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
     * @param Destination $destination
     * @param ClassMapReader $classMapReader
     * @param IgnoredAttributes $ignoredAttributes
     */
    public function __construct(
        Helper $helper,
        Destination $destination,
        ClassMapReader $classMapReader,
        IgnoredAttributes $ignoredAttributes
    ) {
        $this->helper = $helper;
        $this->destination = $destination;
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
                    if (!$this->classMapReader->hasMap($className)
                        || !class_exists($this->classMapReader->convertClassName($className))
                    ) {
                        $classMapFailed[] = [
                            'document' => $tableName,
                            'field' => $field,
                            'error' => sprintf(
                                'Class %s does not exist or not mapped. Record %s=%s',
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
        $destinationFields = $this->destination->getDocument($documentName)->getStructure()->getFields();
        foreach ($destinationFields as $params) {
            if ($params['PRIMARY']) {
                $this->tableKeys[$documentName] = $params['COLUMN_NAME'];
                break;
            }
        }
        return $this->tableKeys[$documentName];
    }
}
