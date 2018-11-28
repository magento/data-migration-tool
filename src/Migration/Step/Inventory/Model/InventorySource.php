<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Inventory\Model;

use Migration\ResourceModel\Destination;

/**
 * Class InventorySource
 */
class InventorySource
{
    /**
     * Destination resource
     *
     * @var Destination
     */
    private $destination;

    /**
     * @var string
     */
    private $defaultSourceCode = '';

    /**
     * @var string
     */
    private $sourceTable = 'inventory_source';

    /**
     * @var array
     */
    private $defaultField = 'source_code';

    /**
     * @param Destination $destination
     */
    public function __construct(
        Destination $destination
    ) {
        $this->destination = $destination;
    }

    /**
     * Get default source code
     *
     * @return string
     */
    public function getDefaultSourceCode()
    {
        if ($this->defaultSourceCode) {
            return $this->defaultSourceCode;
        }
        /** @var \Magento\Framework\DB\Select $select */
        $select = $this->destination->getAdapter()->getSelect()
            ->from($this->destination->addDocumentPrefix($this->sourceTable), [$this->defaultField]);
        $this->defaultSourceCode = $select->getAdapter()->fetchOne($select);

        return $this->defaultSourceCode;
    }
}
