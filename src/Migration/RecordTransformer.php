<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration;

use Migration\Handler\AbstractHandler;
use Migration\Reader\MapInterface;
use Migration\ResourceModel\Record;

/**
 * Class RecordTransformer
 */
class RecordTransformer
{
    /**
     * @var ResourceModel\Document
     */
    protected $sourceDocument;

    /**
     * @var ResourceModel\Document
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
     * @var MapInterface
     */
    protected $mapReader;

    /**
     * @param ResourceModel\Document $sourceDocument
     * @param ResourceModel\Document $destDocument
     * @param Handler\ManagerFactory $handlerManagerFactory
     * @param MapInterface $mapReader
     */
    public function __construct(
        ResourceModel\Document $sourceDocument,
        ResourceModel\Document $destDocument,
        Handler\ManagerFactory $handlerManagerFactory,
        MapInterface $mapReader
    ) {
        $this->sourceDocument = $sourceDocument;
        $this->destDocument = $destDocument;
        $this->handlerManagerFactory = $handlerManagerFactory;
        $this->mapReader = $mapReader;
    }

    /**
     * Transform
     *
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
     * Init
     *
     * @return $this
     */
    public function init()
    {
        $this->sourceHandlerManager = $this->initHandlerManager(MapInterface::TYPE_SOURCE);
        $this->destHandlerManager = $this->initHandlerManager(MapInterface::TYPE_DEST);
        return $this;
    }

    /**
     * Init handler manager
     *
     * @param string $type
     * @return Handler\Manager
     */
    protected function initHandlerManager($type = MapInterface::TYPE_SOURCE)
    {
        /** @var ResourceModel\Document $document */
        $document = (MapInterface::TYPE_SOURCE == $type) ? $this->sourceDocument : $this->destDocument;
        /** @var Handler\Manager $handlerManager */
        $handlerManager = $this->handlerManagerFactory->create();
        $fields = $document->getStructure()->getFields();
        foreach (array_keys($fields) as $field) {
            $handlerConfigs = $this->mapReader->getHandlerConfigs($document->getName(), $field, $type);
            foreach ($handlerConfigs as $handlerConfig) {
                $handlerKey = md5($field . $handlerConfig['class']);
                $handlerManager->initHandler($field, $handlerConfig, $handlerKey);
            }
        }
        return $handlerManager;
    }

    /**
     * Apply handlers
     *
     * @param Handler\Manager $handlerManager
     * @param Record $recordToHandle
     * @param Record $oppositeRecord
     * @return void
     */
    public function applyHandlers(Handler\Manager $handlerManager, Record $recordToHandle, Record $oppositeRecord)
    {
        foreach ($handlerManager->getHandlers() as $handler) {
            /** @var $handler AbstractHandler */
            $handler->handle($recordToHandle, $oppositeRecord);
        }
    }

    /**
     * Copy
     *
     * @param Record $from
     * @param Record $to
     * @return void
     */
    protected function copy(Record $from, Record $to)
    {
        $sourceDocumentName = $this->sourceDocument->getName();
        $sourceFields = $from->getFields();
        $sourceFieldsExtra = array_diff($sourceFields, $to->getFields());
        $data = [];
        foreach ($sourceFields as $key => $field) {
            if ($this->mapReader->isFieldIgnored($sourceDocumentName, $field, MapInterface::TYPE_SOURCE)) {
                continue;
            }
            $fieldMap = $this->mapReader->getFieldMap($sourceDocumentName, $field, MapInterface::TYPE_SOURCE);
            if ($fieldMap == $field && in_array($field, $sourceFieldsExtra)) {
                continue;
            }
            $data[$fieldMap] = $from->getValue($field);
        }
        foreach ($data as $key => $value) {
            $to->setValue($key, $value);
        }
    }
}
