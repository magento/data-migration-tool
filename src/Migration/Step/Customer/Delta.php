<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Customer;

use Migration\App\Step\AbstractDelta;
use Migration\Logger\Logger;
use Migration\Reader\GroupsFactory;
use Migration\Reader\MapFactory;
use Migration\ResourceModel\Source;
use Migration\ResourceModel;
use Migration\Reader\MapInterface;
use Migration\Step\Customer\Model;

/**
 * Class Delta
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class Delta extends AbstractDelta
{
    /**
     * @var string
     */
    protected $mapConfigOption = 'customer_map_file';

    /**
     * @var string
     */
    protected $groupName = 'delta_customer';

    /**
     * @var \Migration\Reader\Groups
     */
    private $readerGroups;

    /**
     * @var Model\AttributesDataToSkip
     */
    private $attributesDataToSkip;

    /**
     * @var Model\AttributesDataToCustomerEntityRecords
     */
    private $attributesDataToCustomerEntityRecords;

    /**
     * @param Source $source
     * @param MapFactory $mapFactory
     * @param GroupsFactory $groupsFactory
     * @param Logger $logger
     * @param ResourceModel\Destination $destination
     * @param ResourceModel\RecordFactory $recordFactory
     * @param \Migration\RecordTransformerFactory $recordTransformerFactory
     * @param Model\AttributesDataToSkip $attributesDataToSkip
     * @param Model\AttributesDataToCustomerEntityRecords $attributesDataToCustomerEntityRecords
     */
    public function __construct(
        Source $source,
        MapFactory $mapFactory,
        GroupsFactory $groupsFactory,
        Logger $logger,
        ResourceModel\Destination $destination,
        ResourceModel\RecordFactory $recordFactory,
        \Migration\RecordTransformerFactory $recordTransformerFactory,
        Model\AttributesDataToSkip $attributesDataToSkip,
        Model\AttributesDataToCustomerEntityRecords $attributesDataToCustomerEntityRecords
    ) {
        $this->readerGroups = $groupsFactory->create('customer_document_groups_file');
        $this->attributesDataToSkip = $attributesDataToSkip;
        $this->attributesDataToCustomerEntityRecords = $attributesDataToCustomerEntityRecords;
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
     * @inheritdoc
     */
    protected function processChangedRecords($documentName, $idKeys)
    {
        $items = $this->source->getChangedRecords($documentName, $idKeys);
        if (empty($items)) {
            return;
        }
        if (!$this->eolOnce) {
            $this->eolOnce = true;
            echo PHP_EOL;
        }
        $skippedAttributes = array_keys($this->attributesDataToSkip->getSkippedAttributes());
        $sourceEntityDocuments = array_keys($this->readerGroups->getGroup('source_entity_documents'));
        $destinationName = $this->mapReader->getDocumentMap($documentName, MapInterface::TYPE_SOURCE);
        $sourceDocument = $this->source->getDocument($documentName);
        $destDocument = $this->destination->getDocument($destinationName);
        $recordTransformer = $this->getRecordTransformer($sourceDocument, $destDocument);
        do {
            $destinationRecords = $destDocument->getRecords();
            foreach ($items as $data) {
                echo('.');
                if (isset($data['attribute_id']) && in_array($data['attribute_id'], $skippedAttributes)) {
                    continue;
                }
                $this->transformData(
                    $data,
                    $sourceDocument,
                    $destDocument,
                    $recordTransformer,
                    $destinationRecords
                );
            }
            if (in_array($documentName, $sourceEntityDocuments)) {
                $this->attributesDataToCustomerEntityRecords
                    ->updateCustomerEntities($documentName, $destinationRecords);
            }
            $this->destination->updateChangedRecords($destinationName, $destinationRecords);
            $documentNameDelta = $this->source->getDeltaLogName($documentName);
            $documentNameDelta = $this->source->addDocumentPrefix($documentNameDelta);
            $this->markRecordsProcessed($documentNameDelta, $idKeys, $items);
        } while (!empty($items = $this->source->getChangedRecords($documentName, $idKeys)));
    }
}
