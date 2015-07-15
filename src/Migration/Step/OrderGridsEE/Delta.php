<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\OrderGridsEE;

use Migration\Logger\Logger;
use Migration\Reader\GroupsFactory;
use Migration\Resource\Source;

class Delta extends \Migration\Step\OrderGrids\Delta
{
    /**
     * @param Source $source
     * @param GroupsFactory $groupsFactory
     * @param Logger $logger
     * @param Helper $helper
     * @param Data $data
     */
    public function __construct(
        Source $source,
        GroupsFactory $groupsFactory,
        Logger $logger,
        Helper $helper,
        Data $data
    ) {
        $this->source = $source;
        $this->readerGroups = $groupsFactory->create('order_grids_document_groups_file');
        $this->logger = $logger;
        $this->helper = $helper;
        $this->data = $data;
        parent::__construct($source, $groupsFactory, $logger, $helper, $data);
    }
}
