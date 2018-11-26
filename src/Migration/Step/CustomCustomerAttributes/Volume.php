<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\CustomCustomerAttributes;

use Migration\App\Step\AbstractVolume;
use Migration\App\ProgressBar;
use Migration\Reader\Groups;
use Migration\Reader\GroupsFactory;
use Migration\Reader\Map;
use Migration\Reader\MapFactory;
use Migration\Reader\MapInterface;
use Migration\ResourceModel\Destination;
use Migration\ResourceModel\Source;
use Migration\Logger\Logger;

/**
 * Class Volume
 */
class Volume extends AbstractVolume
{
    /**
     * @var Source
     */
    protected $source;

    /**
     * @var Destination
     */
    protected $destination;

    /**
     * @var ProgressBar\LogLevelProcessor
     */
    protected $progress;

    /**
     * @var Map
     */
    protected $map;

    /**
     * @var Groups
     */
    protected $groups;

    /**
     * @param Source $source
     * @param Destination $destination
     * @param ProgressBar\LogLevelProcessor $progress
     * @param MapFactory $mapFactory
     * @param GroupsFactory $groupsFactory
     * @param Logger $logger
     */
    public function __construct(
        Source $source,
        Destination $destination,
        ProgressBar\LogLevelProcessor $progress,
        MapFactory $mapFactory,
        GroupsFactory $groupsFactory,
        Logger $logger
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->progress = $progress;
        $this->groups = $groupsFactory->create('customer_attr_document_groups_file');
        $this->map = $mapFactory->create('customer_attr_map_file');
        parent::__construct($logger);
    }

    /**
     * @inheritdoc
     */
    public function perform()
    {
        $sourceDocuments = array_keys($this->groups->getGroup('source_documents'));
        $this->progress->start(count($sourceDocuments));
        foreach ($sourceDocuments as $sourceName) {
            $this->progress->advance();
            $destinationName = $this->map->getDocumentMap($sourceName, MapInterface::TYPE_SOURCE);
            $sourceFields = $this->source->getDocument($sourceName)->getStructure()->getFields();
            $destinationFields = $this->destination->getDocument($destinationName)->getStructure()->getFields();
            if (!empty(array_diff_key($sourceFields, $destinationFields))) {
                $this->errors[] = 'Mismatch of fields in the document: ' . $destinationName;
            }
            $sourceCount = $this->source->getRecordsCount($sourceName);
            $destinationCount = $this->destination->getRecordsCount($destinationName);
            if ($sourceCount != $destinationCount) {
                $this->errors[] = sprintf(
                    'Mismatch of entities in the document: %s Source: %s Destination: %s',
                    $destinationName,
                    $sourceCount,
                    $destinationCount
                );
            }
        }
        $this->progress->finish();
        return $this->checkForErrors();
    }
}
