<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\SalesOrder;

use Migration\Resource\Source;

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
     * @param string $eavAttribute
     * @return mixed
     */
    public function getSourceAttributes($eavAttribute)
    {
        $select = $this->getEavAttributeSelect($eavAttribute);
        return $this->source->getAdapter()->loadDataFromSelect($select);
    }

    /**
     * @param string $eavAttribute
     * @return \Magento\Framework\DB\Select
     */
    protected function getEavAttributeSelect($eavAttribute)
    {
        /** @var \Migration\Resource\Adapter\Mysql $adapter */
        $adapter = $this->source->getAdapter();
        $select = $adapter->getSelect();
        $select->from(array_keys($this->getDocumentList()))->where($eavAttribute . ' is not null');
        return $select;
    }

    /**
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
     * @return array
     */
    public function getDocumentList()
    {
        return ['sales_flat_order' => 'sales_order'];
    }


    /**
     * @return string
     */
    public function getDestEavDocument()
    {
        return 'eav_entity_int';
    }
}
