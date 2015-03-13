<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Integrity;

use Migration\Logger\Logger;
use Migration\MapReaderInterface;
use Migration\ProgressBar;
use Migration\Resource;

/**
 * Class AbstractIntegrity
 */
abstract class AbstractIntegrity
{
    /**
     * Resource of source
     *
     * @var Resource\Source
     */
    protected $source;

    /**
     * Resource of destination
     *
     * @var Resource\Destination
     */
    protected $destination;

    /**
     * Logger instance
     *
     * @var Logger
     */
    protected $logger;

    /**
     * Missing documents
     *
     * @var array
     */
    protected $missingDocuments;

    /**
     * Missing document fields
     *
     * @var array
     */
    protected $missingDocumentFields;

    /**
     * Map reader
     *
     * @var MapReaderInterface
     */
    protected $map;

    /**
     * ProgressBar instance
     *
     * @var ProgressBar
     */
    protected $progress;

    /**
     * @param ProgressBar $progress
     * @param Logger $logger
     * @param Resource\Source $source
     * @param Resource\Destination $destination
     * @param MapReaderInterface $mapReader
     */
    public function __construct(
        ProgressBar $progress,
        Logger $logger,
        Resource\Source $source,
        Resource\Destination $destination,
        MapReaderInterface $mapReader
    ) {
        $this->logger = $logger;
        $this->progress = $progress;
        $this->source = $source;
        $this->destination = $destination;
        $this->map = $mapReader;
    }

    /**
     * Peform Integrity Check
     *
     * @return mixed
     */
    abstract public function perform();

    /**
     * Returns number of iterations for integrity check
     * @return mixed
     */
    abstract protected function getIterationsCount();

    /**
     * Check if source and destination resources have equal document names and fields
     *
     * @param array $documents
     * @param string $type - allowed values: MapReaderInterface::TYPE_SOURCE, MapReaderInterface::TYPE_DEST
     * @return $this
     * @throws \Exception
     */
    protected function check($documents, $type)
    {
        $source = $type == MapReaderInterface::TYPE_SOURCE ? $this->source : $this->destination;
        $destination = $type == MapReaderInterface::TYPE_SOURCE ? $this->destination : $this->source;

        $destDocuments = array_flip($destination->getDocumentList());
        foreach ($documents as $document) {
            $this->progress->advance();
            $mappedDocument = $this->map->getDocumentMap($document, $type);
            if ($mappedDocument !== false) {
                if (!isset($destDocuments[$mappedDocument])) {
                    $this->missingDocuments[$type][$document] = true;
                } else {
                    $fields = array_keys($source->getDocument($document)->getStructure()->getFields());
                    $destFields = $destination->getDocument($mappedDocument)->getStructure()->getFields();
                    foreach ($fields as $field) {
                        $mappedField = $this->map->getFieldMap($document, $field, $type);
                        if ($mappedField && !isset($destFields[$mappedField])) {
                            $this->missingDocumentFields[$type][$document][] = $mappedField;
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Process missing entities and log them in to the file
     *
     * @return $this
     */
    protected function checkForErrors()
    {
        $isSuccess = true;
        if (isset($this->missingDocuments['source'])) {
            $isSuccess = false;
            $this->logger->error(sprintf(
                PHP_EOL . 'Next documents from source are not mapped:%s',
                PHP_EOL . implode(',', array_keys($this->missingDocuments['source']))
            ));
        }
        if (isset($this->missingDocuments['destination'])) {
            $isSuccess = false;
            $this->logger->error(sprintf(
                PHP_EOL . 'Next documents from destination are not mapped:%s',
                PHP_EOL . implode(',', array_keys($this->missingDocuments['destination']))
            ));
        }
        $errorMsgFields = '';
        if (isset($this->missingDocumentFields['source'])) {
            $isSuccess = false;
            foreach ($this->missingDocumentFields['source'] as $document => $fields) {
                $errorMsgFields .= sprintf(
                    PHP_EOL . 'Document name: %s; Fields: %s',
                    $document,
                    implode(',', $fields)
                );
            }
            $this->logger->error(
                PHP_EOL . 'Next fields from source are not mapped:' . $errorMsgFields
            );
        }
        $errorMsgFields = '';
        if (isset($this->missingDocumentFields['destination'])) {
            $isSuccess = false;
            foreach ($this->missingDocumentFields['destination'] as $document => $fields) {
                $errorMsgFields .= sprintf(
                    PHP_EOL . 'Document name: %s; Fields: %s',
                    $document,
                    implode(',', $fields)
                );
            }
            $this->logger->error(
                PHP_EOL . 'Next fields from destination are not mapped:' . $errorMsgFields
            );
        }
        return $isSuccess;
    }
}
