<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\UrlRewrite\Model;

use Magento\Framework\Db\Select as DbSelect;
use Migration\ResourceModel\Source;
use \Migration\ResourceModel\Adapter\Mysql as AdapterMysql;

/**
 * Class TemporaryTable creates a table where all url rewrites will be collected
 */
class TemporaryTable
{
    /**
     * @var Source
     */
    private $source;

    /**
     * @var AdapterMysql
     */
    private $sourceAdapter;

    /**
     * @var string
     */
    private $tableName = '';

    /**
     * @param Source $source
     */
    public function __construct(
        Source $source
    ) {
        $this->source = $source;
        $this->sourceAdapter = $this->source->getAdapter();
        $this->tableName = 'url_rewrite_m2' . md5('url_rewrite_m2');
    }

    /**
     * Return name of temporary table
     *
     * @return string
     */
    public function getName()
    {
        return $this->tableName;
    }

    /**
     * Crete temporary table
     *
     * @return void
     */
    public function create()
    {
        $select = $this->sourceAdapter->getSelect();
        $select->getAdapter()->dropTable($this->source->addDocumentPrefix($this->tableName));
        /** @var \Magento\Framework\DB\Ddl\Table $table */
        $table = $select->getAdapter()->newTable($this->source->addDocumentPrefix($this->tableName))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn(
                'request_path',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                'target_path',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255
            )
            ->addColumn(
                'is_system',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '0']
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER
            )
            ->addColumn(
                'entity_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32
            )
            ->addColumn(
                'redirect_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0']
            )
            ->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER
            )
            ->addColumn(
                'category_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER
            )
            ->addColumn(
                'cms_page_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER
            )
            ->addColumn(
                'priority',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER
            )
            ->addIndex(
                'url_rewrite',
                ['request_path', 'target_path', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )        ;
        $select->getAdapter()->createTable($table);
    }
}
