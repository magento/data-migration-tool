<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Map;

use Migration\Logger\Logger;
use Migration\MapReader;
use Migration\Resource;

class Integrity
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
     * @var MapReader
     */
    protected $map;

    /**
     * @param Logger $logger
     * @param Resource\Source $source
     * @param Resource\Destination $destination
     * @param MapReader $mapReader
     * @param \Migration\Config $config
     */
    public function __construct(
        Logger $logger,
        Resource\Source $source,
        Resource\Destination $destination,
        MapReader $mapReader,
        \Migration\Config $config
    ) {
        $this->logger = $logger;
        $this->source = $source;
        $this->destination = $destination;
        $this->map = $mapReader;
        $this->map->init($config->getOption('map_file'));
    }

    /**
     * {@inheritdoc}
     */
    public function perform()
    {
        $this->check(MapReader::TYPE_SOURCE);
        $this->check(MapReader::TYPE_DEST);
        return $this->checkForErrors();
    }

    /**
     * Check if source and destination resources have equal document names and fields
     *
     * @param string $type - allowed values: MapReader::TYPE_SOURCE, MapReader::TYPE_DEST
     * @return $this
     * @throws \Exception
     */
    protected function check($type)
    {
        $source = $type == MapReader::TYPE_SOURCE ? $this->source : $this->destination;
        $destination = $type == MapReader::TYPE_SOURCE ? $this->destination : $this->source;

        $sourceDocuments = $source->getDocumentList();
        $destDocuments = array_flip($destination->getDocumentList());
        foreach ($sourceDocuments as $document) {
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
