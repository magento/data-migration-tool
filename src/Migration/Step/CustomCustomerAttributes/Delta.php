<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\CustomCustomerAttributes;

use Migration\App\Step\AbstractDelta;
use Migration\App\Step\StageInterface;
use Migration\Reader\GroupsFactory;
use Migration\Reader\MapFactory;
use Migration\Resource;
use Migration\Resource\Source;
use Migration\Logger\Logger;

/**
 * Class CustomerAttributesSalesFlat
 */
class Delta extends AbstractDelta implements StageInterface
{
    /**
     * @param Source $source
     * @param MapFactory $mapFactory
     * @param GroupsFactory $groupsFactory
     * @param Logger $logger
     * @param Resource\Destination $destination
     * @param Resource\RecordFactory $recordFactory
     * @param \Migration\RecordTransformerFactory $recordTransformerFactory
     * @param string $mapConfigOption
     * @param string $groupName
     */
    public function __construct(
        Source $source,
        MapFactory $mapFactory,
        GroupsFactory $groupsFactory,
        Logger $logger,
        Resource\Destination $destination,
        Resource\RecordFactory $recordFactory,
        \Migration\RecordTransformerFactory $recordTransformerFactory,
        $mapConfigOption = 'customer_attr_map_file',
        $groupName = 'delta_customer_custom_attributes'
    ) {
        parent::__construct(
            $source,
            $mapFactory,
            $groupsFactory,
            $logger,
            $destination,
            $recordFactory,
            $recordTransformerFactory,
            $mapConfigOption,
            $groupName
        );
    }
}
