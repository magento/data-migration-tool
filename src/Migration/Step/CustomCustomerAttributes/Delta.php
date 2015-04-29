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
     * @var string
     */
    protected $mapConfigOption = 'customer_attr_map_file';

    /**
     * @var string
     */
    protected $groupName = 'delta_customer_custom_attributes';

    /**
     * @param Source $source
     * @param MapFactory $mapFactory
     * @param GroupsFactory $groupsFactory
     * @param Logger $logger
     * @param Resource\Destination $destination
     * @param Resource\RecordFactory $recordFactory
     * @param \Migration\RecordTransformerFactory $recordTransformerFactory
     */
    public function __construct(
        Source $source,
        MapFactory $mapFactory,
        GroupsFactory $groupsFactory,
        Logger $logger,
        Resource\Destination $destination,
        Resource\RecordFactory $recordFactory,
        \Migration\RecordTransformerFactory $recordTransformerFactory
    ) {
        parent::__construct(
            $source,
            $mapFactory,
            $groupsFactory,
            $logger,
            $destination,
            $recordFactory,
            $recordTransformerFactory
        );
    }
}
