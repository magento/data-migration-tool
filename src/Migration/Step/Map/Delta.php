<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Map;

use Migration\App\Step\AbstractDelta;
use Migration\Logger\Logger;
use Migration\Reader\GroupsFactory;
use Migration\Reader\MapFactory;
use Migration\ResourceModel\Source;
use Migration\ResourceModel\Destination;
use Migration\ResourceModel;

class Delta extends AbstractDelta
{
    /**
     * @var string
     */
    protected $mapConfigOption = 'map_file';

    /**
     * @var string
     */
    protected $groupName = 'delta_map';

    /**
     * @var Data
     */
    protected $data;

    /**
     * @param Source $source
     * @param MapFactory $mapFactory
     * @param GroupsFactory $groupsFactory
     * @param Logger $logger
     * @param Destination $destination
     * @param ResourceModel\RecordFactory $recordFactory
     * @param \Migration\RecordTransformerFactory $recordTransformerFactory
     * @param Data $data
     * @param Helper
     */
    public function __construct(
        Source $source,
        MapFactory $mapFactory,
        GroupsFactory $groupsFactory,
        Logger $logger,
        Destination $destination,
        ResourceModel\RecordFactory $recordFactory,
        \Migration\RecordTransformerFactory $recordTransformerFactory,
        Data $data,
        Helper $helper
    ) {
        $this->data = $data;
        $this->documentsDuplicateOnUpdate = $helper->getDocumentsDuplicateOnUpdate();
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

    /**
     * @param ResourceModel\Document $sourceDocument
     * @param ResourceModel\Document $destinationDocument
     * @return \Migration\RecordTransformer
     */
    protected function getRecordTransformer($sourceDocument, $destinationDocument)
    {
        return $this->data->getRecordTransformer($sourceDocument, $destinationDocument);
    }
}
