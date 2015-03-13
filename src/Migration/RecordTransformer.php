<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration;

use Migration\Resource\Record;

/**
 * Class RecordTransformer
 */
class RecordTransformer
{
    /**
     * @var Resource\Document
     */
    protected $sourceDocument;

    /**
     * @var Resource\Document
     */
    protected $destDocument;

    /**
     * @var Handler\ManagerFactory
     */
    protected $handlerManagerFactory;

    /**
     * @var Handler\Manager
     */
    protected $sourceHandlerManager;

    /**
     * @var Handler\Manager
     */
    protected $destHandlerManager;

    /**
     * @var MapReaderInterface
     */
    protected $mapReader;

    /**
     * @param Resource\Document $sourceDocument
     * @param Resource\Document $destDocument
     * @param Handler\ManagerFactory $handlerManagerFactory
     * @param MapReaderInterface $mapReader
     */
    public function __construct(
        Resource\Document $sourceDocument,
        Resource\Document $destDocument,
        Handler\ManagerFactory $handlerManagerFactory,
        MapReaderInterface $mapReader
    ) {
        $this->sourceDocument = $sourceDocument;
        $this->destDocument = $destDocument;
        $this->handlerManagerFactory = $handlerManagerFactory;
        $this->mapReader = $mapReader;
    }

    /**
     * @param Record $from
     * @param Record $to
     * @return void
     */
    public function transform(Record $from, Record $to)
    {
        $this->applyHandlers($this->sourceHandlerManager, $from, $to);
        $this->copy($from, $to);
        $this->applyHandlers($this->destHandlerManager, $to, $to);
    }

    /**
     * @return $this
     */
    public function init()
    {
        $this->sourceHandlerManager = $this->initHandlerManager(MapReaderInterface::TYPE_SOURCE);
        $this->destHandlerManager = $this->initHandlerManager(MapReaderInterface::TYPE_DEST);
        return $this;
    }

    /**
     * @param string $type
     * @return Handler\Manager
     */
    protected function initHandlerManager($type = MapReaderInterface::TYPE_SOURCE)
    {
        /** @var Resource\Document $document */
        $document = (MapReaderInterface::TYPE_SOURCE == $type) ? $this->sourceDocument : $this->destDocument;
        /** @var Handler\Manager $handlerManager */
        $handlerManager = $this->handlerManagerFactory->create();
        $fields = $document->getStructure()->getFields();
        foreach (array_keys($fields) as $field) {
            $handlerManager->initHandler(
                $field,
                $this->mapReader->getHandlerConfig($document->getName(), $field, $type)
            );
        }
        return $handlerManager;
    }

    /**
     * @param Handler\Manager $handlerManager
     * @param Record $recordToHandle
     * @param Record $oppositeRecord
     * @return void
     */
    public function applyHandlers(Handler\Manager $handlerManager, Record $recordToHandle, Record $oppositeRecord)
    {
        foreach ($recordToHandle->getFields() as $field) {
            $handler = $handlerManager->getHandler($field);
            if (!empty($handler)) {
                $handler->handle($recordToHandle, $oppositeRecord);
            }
        }
    }

    /**
     * @param Record $from
     * @param Record $to
     * @return void
     */
    protected function copy(Record $from, Record $to)
    {
        foreach ($from->getFields() as $field) {
            if (!$this->mapReader->isFieldIgnored(
                $this->sourceDocument->getName(),
                $field,
                MapReaderInterface::TYPE_SOURCE
            )) {
                $fieldMap = $this->mapReader->getFieldMap(
                    $this->sourceDocument->getName(),
                    $field,
                    MapReaderInterface::TYPE_SOURCE
                );
                $to->setValue($fieldMap, $from->getValue($field));
            }
        }
    }
}
