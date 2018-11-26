<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\SalesOrder;

use Migration\ResourceModel\Source;

/**
 * Class Helper
 */
class Helper
{
    /**
     * @var Source
     */
    protected $source;

    /**
     * @param Source $source
     */
    public function __construct(
        Source $source
    ) {
        $this->source = $source;
    }

    /**
     * Get source attributes
     *
     * @param string $eavAttribute
     * @return mixed
     */
    public function getSourceAttributes($eavAttribute)
    {
        $select = $this->getEavAttributeSelect($eavAttribute);
        return $this->source->getAdapter()->loadDataFromSelect($select);
    }

    /**
     * Get eav attribute select
     *
     * @param string $eavAttribute
     * @return \Magento\Framework\DB\Select
     */
    protected function getEavAttributeSelect($eavAttribute)
    {
        /** @var \Migration\ResourceModel\Adapter\Mysql $adapter */
        $adapter = $this->source->getAdapter();
        $select = $adapter->getSelect();
        $tables = [];
        foreach (array_keys($this->getDocumentList()) as $sourceDocument) {
            $tables[] = $this->source->addDocumentPrefix($sourceDocument);
        }
        $select->from($tables)->where($eavAttribute . ' is not null');
        return $select;
    }

    /**
     * Get eav attributes
     *
     * @return array
     */
    public function getEavAttributes()
    {
        return [
            'reward_points_balance_refunded',
            'reward_salesrule_points'
        ];
    }

    /**
     * Get document list
     *
     * @return array
     */
    public function getDocumentList()
    {
        return ['sales_flat_order' => 'sales_order'];
    }

    /**
     * Get dest eav document
     *
     * @return string
     */
    public function getDestEavDocument()
    {
        return 'eav_entity_int';
    }
}
