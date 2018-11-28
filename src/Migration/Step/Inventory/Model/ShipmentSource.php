<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Inventory\Model;

use Migration\ResourceModel\Destination;
use Magento\Framework\DB\Adapter\Pdo\Mysql;

/**
 * Class ShipmentSource
 */
class ShipmentSource implements TableInterface, InventoryModelInterface
{
    /**
     * Destination resource
     *
     * @var Destination
     */
    private $destination;

    /**
     * @var InventorySource
     */
    private $inventorySource;

    /**
     * @var string
     */
    private $salesShipmentTable = 'sales_shipment';

    /**
     * @var string
     */
    private $shipmentSourceTable = 'inventory_shipment_source';

    /**
     * @var array
     */
    private $shipmentSourceTableFields = [
        'shipment_id',
        'source_code'
    ];

    /**
     * @param Destination $destination
     * @param InventorySource $inventorySource
     */
    public function __construct(
        Destination $destination,
        InventorySource $inventorySource
    ) {
        $this->destination = $destination;
        $this->inventorySource = $inventorySource;
    }

    /**
     * @inheritdoc
     */
    public function prepareSelect()
    {
        /** @var \Magento\Framework\DB\Select $select */
        $select = $this->destination->getAdapter()->getSelect()
            ->from(
                [$this->getSourceTableName()],
                [
                    'shipment_id' => 'entity_id',
                    'source_code' => new \Zend_Db_Expr("'" . $this->inventorySource->getDefaultSourceCode() . "'"),
                ]
            );
        return $select;
    }

    /**
     * @inheritdoc
     */
    public function insertFromSelect(\Magento\Framework\DB\Select $select)
    {
        /** @var \Migration\ResourceModel\Adapter\Mysql $adapter */
        $adapter = $this->destination->getAdapter();
        $adapter->insertFromSelect(
            $select,
            $this->getDestinationTableName(),
            $this->getDestinationTableFields(),
            Mysql::INSERT_ON_DUPLICATE
        );
    }

    /**
     * @inheritdoc
     */
    public function getDestinationTableName()
    {
        return $this->destination->addDocumentPrefix($this->shipmentSourceTable);
    }

    /**
     * @inheritdoc
     */
    public function getDestinationTableFields()
    {
        return $this->shipmentSourceTableFields;
    }

    /**
     * @inheritdoc
     */
    public function getSourceTableName()
    {
        return $this->destination->addDocumentPrefix($this->salesShipmentTable);
    }
}
