<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\SalesOrder;

use Migration\ResourceModel\Destination;
use Migration\ResourceModel\Source;

/**
 * Class InitialData
 */
class InitialData
{
    /**
     * @var int $destEavAttributesCount
     */
    protected $destEavAttributesCount;

    /**
     * @var Source
     */
    protected $source;

    /**
     * @var Destination
     */
    protected $destination;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param Source $source
     * @param Destination $destination
     * @param Helper $helper
     */
    public function __construct(Source $source, Destination $destination, Helper $helper)
    {
        $this->source = $source;
        $this->destination = $destination;
        $this->helper = $helper;
        $this->init();
    }

    /**
     * Load EAV data before migration
     *
     * @return void
     */
    public function init()
    {
        $this->initDestAttributes($this->helper->getDestEavDocument());
    }

    /**
     * Init dest attributes
     *
     * @param string $eavEntity
     * @return void
     */
    protected function initDestAttributes($eavEntity)
    {
        if (!isset($this->destEavAttributesCount[$eavEntity])) {
            $this->destEavAttributesCount[$eavEntity] = (int)$this->destination->getRecordsCount($eavEntity);
        }
    }

    /**
     * Get dest eav attributes count
     *
     * @param string $eavEntity
     * @return int
     */
    public function getDestEavAttributesCount($eavEntity)
    {
        $attributesCount = null;
        if (isset($this->destEavAttributesCount[$eavEntity])) {
            $attributesCount = $this->destEavAttributesCount[$eavEntity];
        }
        return $attributesCount;
    }
}
