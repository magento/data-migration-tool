<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Map;

use Migration\App\Step\AbstractDelta;
use Migration\Logger\Logger;
use Migration\Reader\GroupsFactory;
use Migration\Reader\MapFactory;
use Migration\Resource\Source;
use Migration\Resource\Destination;
use Migration\Resource;

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
     * @param Resource\RecordFactory $recordFactory
     * @param \Migration\RecordTransformerFactory $recordTransformerFactory
     * @param Data $data
     */
    public function __construct(
        Source $source,
        MapFactory $mapFactory,
        GroupsFactory $groupsFactory,
        Logger $logger,
        Destination $destination,
        Resource\RecordFactory $recordFactory,
        \Migration\RecordTransformerFactory $recordTransformerFactory,
        Data $data
    ) {
        $this->data = $data;
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
     * @param Resource\Document $sourceDocument
     * @param Resource\Document $destinationDocument
     * @return \Migration\RecordTransformer
     */
    protected function getRecordTransformer($sourceDocument, $destinationDocument)
    {
        return $this->data->getRecordTransformer($sourceDocument, $destinationDocument);
    }
}
