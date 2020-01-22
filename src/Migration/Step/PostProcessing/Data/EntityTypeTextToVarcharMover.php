<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\PostProcessing\Data;

use Migration\ResourceModel;
use Migration\App\ProgressBar;
use Migration\App\Progress;
use Migration\Config;

/**
 * Class EntityTypeTextToVarcharMover
 */
class EntityTypeTextToVarcharMover
{
    /**
     * @var ResourceModel\Destination
     */
    private $destination;

    /**
     * @var ProgressBar\LogLevelProcessor
     */
    private $progressBar;

    /**
     * @var Progress
     */
    private $progress;

    /**
     * @var string
     */
    private $productTextTypeTable = 'catalog_product_entity_text';

    /**
     * @var string
     */
    private $productVarcharTypeTable = 'catalog_product_entity_varchar';

    /**
     * @var string
     */
    private $eavAttributesTable = 'eav_attribute';

    /**
     * @var string
     */
    private $eavEntityTypeTable = 'eav_entity_type';

    /**
     * @var string
     */
    private $productEntityTypeCode = 'catalog_product';

    /**
     * @var string
     */
    protected $editionMigrate = '';


    /**
     * @param ProgressBar\LogLevelProcessor $progressBar
     * @param ResourceModel\Destination $destination
     * @param Progress $progress
     * @param Config $config
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progressBar,
        ResourceModel\Destination $destination,
        Progress $progress,
        Config $config
    ) {
        $this->destination = $destination;
        $this->progressBar = $progressBar;
        $this->progress = $progress;
        $this->editionMigrate = $config->getOption('edition_migrate');
    }

    /**
     * Moves records of multiselect attributes from text type table to varchar type table
     *
     * @return void
     */
    public function move()
    {
        /** @var \Migration\ResourceModel\Adapter\Mysql $adapter */
        $adapter = $this->destination->getAdapter();
        $select = $adapter->getSelect()
            ->from(
                ['ea' => $this->destination->addDocumentPrefix($this->eavAttributesTable)],
                ['attribute_id']

            )->join(
                ['eet' => $this->destination->addDocumentPrefix($this->eavEntityTypeTable)],
                'eet.entity_type_id = ea.entity_type_id',
                []
            )->where(
                'eet.entity_type_code = ?',
                $this->productEntityTypeCode
            )->where(
                'ea.frontend_input = "multiselect"'
            );
        $multiselectIds = $adapter->getSelect()->getAdapter()->fetchCol($select);
        $entityIdName = $this->editionMigrate == Config::EDITION_MIGRATE_OPENSOURCE_TO_OPENSOURCE
            ? 'entity_id'
            : 'row_id';
        $fields = ['attribute_id', 'store_id', 'value', $entityIdName];
        $select = $adapter->getSelect()
            ->from(
                ['cpet' => $this->destination->addDocumentPrefix($this->productTextTypeTable)],
                $fields
            )->where(
                'cpet.attribute_id in (?)', $multiselectIds
            );
        $this->destination->getAdapter()->insertFromSelect(
            $select,
            $this->destination->addDocumentPrefix($this->productVarcharTypeTable),
            $fields,
            \Magento\Framework\Db\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
        );
        if ($multiselectIds) {
            $this->destination->deleteRecords(
                $this->destination->addDocumentPrefix($this->productTextTypeTable),
                'attribute_id',
                $multiselectIds
            );
        }
    }

    /**
     * Get documents
     *
     * @return array
     */
    public function getDocuments()
    {
        return [
            $this->productTextTypeTable,
            $this->productVarcharTypeTable
        ];
    }
}
