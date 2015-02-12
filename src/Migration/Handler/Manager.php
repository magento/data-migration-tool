<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

use Magento\Framework\ObjectManagerInterface;
use Migration\MapReader;
use Migration\Resource\Document;
use Migration\Resource\Record;
use Migration\Resource\Record\Collection;
use Migration\Resource\Structure;

class Manager
{
    /**
     * @var HandlerInterface[]
     */
    protected $handlers = [];

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Migration\Resource\Record\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Migration\Resource\RecordFactory
     */
    protected $recordFactory;

    /**
     * @var Structure
     */
    protected $srcStructure;

    /**
     * @var Structure
     */
    protected $destStructure;

    /**
     * @var MapReader
     */
    protected $mapReader;

    /**
     * @var array;
     */
    protected $fieldMap = [];

    /**
     * @param \Migration\Resource\RecordFactory $recordFactory
     * @param \Migration\Resource\Record\CollectionFactory $collectionFactory
     * @param ObjectManagerInterface $objectManager
     * @param MapReader $mapReader
     * @throws \Exception
     */
    public function __construct(
        \Migration\Resource\RecordFactory $recordFactory,
        \Migration\Resource\Record\CollectionFactory $collectionFactory,
        ObjectManagerInterface $objectManager,
        MapReader $mapReader
    ) {
        $this->recordFactory = $recordFactory;
        $this->collectionFactory = $collectionFactory;
        $this->objectManager = $objectManager;
        $this->mapReader = $mapReader;
        $this->mapReader->init();
    }

    /**
     * @param string $field
     * @param string $type
     * @param string $handlerName
     * @param array $params
     * @return void
     * @throws \Exception
     */
    public function addHandler($field, $type, $handlerName, array $params = [])
    {
        $handler = $this->objectManager->create($handlerName, $params);
        if (!$handler instanceof HandlerInterface) {
            throw new \Exception("'$handlerName' is not correct handler.");
        }
        $this->handlers[$type][$field][] = $handler;
    }

    /**
     * Adding field map
     *
     * @param string $srcName
     * @param string|null $destName
     * @return void
     */
    public function addFieldMap($srcName, $destName)
    {
        $this->fieldMap[$srcName] = $destName;
    }

    /**
     * @param Document $sourceDocument
     * @param Document $destinationDocument
     * @return $this
     * @throws \Exception
     */
    public function init(Document $sourceDocument, Document $destinationDocument)
    {
        $this->srcStructure = $sourceDocument->getStructure();
        $this->destStructure = $destinationDocument->getStructure();
        $sourceName = $sourceDocument->getName();
        foreach (array_keys($sourceDocument->getStructure()->getFields()) as $field) {
            if ($this->mapReader->isFieldIgnored($sourceName, $field, MapReader::TYPE_SOURCE)) {
                $this->addFieldMap($field, null);
                continue;
            }

            $destField = $this->mapReader->getFieldMap($sourceName, $field, MapReader::TYPE_SOURCE);
            if (!$destField) {
                $destField = $field;
            }
            $this->addFieldMap($field, $destField);
            $handlerConfig = $this->mapReader->getHandlerConfig($sourceName, $field, MapReader::TYPE_SOURCE);
            if (!empty($handlerConfig)) {
                $this->addHandler(
                    $field,
                    MapReader::TYPE_SOURCE,
                    $handlerConfig['class'],
                    $handlerConfig['params']
                );
            }
        }
        foreach (array_keys($destinationDocument->getStructure()->getFields()) as $field) {
            $handlerConfig = $this->mapReader->getHandlerConfig($sourceName, $field, MapReader::TYPE_DEST);
            if (!empty($handlerConfig)) {
                $this->addHandler(
                    $field,
                    MapReader::TYPE_DEST,
                    $handlerConfig['class'],
                    $handlerConfig['params']
                );
            }
        }
        return $this;
    }

    /**
     * @param Collection $recordsCollection
     * @return Collection
     */
    public function process(Collection $recordsCollection)
    {
        /** @var Collection $destCollection */
        $destCollection = $this->collectionFactory->create(['structure' => $this->destStructure]);
        /** @var Record $srcRecord */
        foreach ($recordsCollection as $srcRecord) {
            /** @var Record $destRecord */
            $destRecord = $this->recordFactory->create();
            $destRecord->setStructure($this->destStructure);
            foreach (array_keys($this->srcStructure->getFields()) as $fieldName) {
                if (empty($this->fieldMap[$fieldName])) {
                    continue;
                }
                if (isset($this->handlers[\Migration\MapReader::TYPE_SOURCE][$fieldName])) {
                    /** @var HandlerInterface $handler */
                    foreach ($this->handlers[\Migration\MapReader::TYPE_SOURCE][$fieldName] as $handler) {
                        $handler->handle($srcRecord, $fieldName);
                    }
                }
                $destRecord->setValue($this->fieldMap[$fieldName], $srcRecord->getValue($fieldName));
            }
            foreach (array_keys($this->destStructure->getFields()) as $fieldName) {
                if (isset($this->handlers[\Migration\MapReader::TYPE_DEST][$fieldName])) {
                    /** @var HandlerInterface $handler */
                    foreach ($this->handlers[\Migration\MapReader::TYPE_DEST][$fieldName] as $handler) {
                        $handler->handle($destRecord, $fieldName);
                    }
                }
            }
            $destCollection->addRecord($destRecord);
        }
        return $destCollection;
    }
}
