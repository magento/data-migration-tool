<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

use Migration\Logger\Logger;
use Migration\MapReader;
use Migration\Resource;
use Migration\Config;

/**
 * Class Integrity
 */
class Integrity extends AbstractStep
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
     * @var Config
     */
    protected $config;

    /**
     * @param Progress $progress
     * @param Logger $logger
     * @param Resource\Source $source
     * @param Resource\Destination $destination
     * @param MapReader $mapReader
     * @param Config $config
     */
    public function __construct(
        Progress $progress,
        Logger $logger,
        Resource\Source $source,
        Resource\Destination $destination,
        MapReader $mapReader,
        Config $config
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->map = $mapReader;
        $this->config = $config;
        parent::__construct($progress, $logger);
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        parent::run();
        $this->progress->reset();
        $this->progress->start($this->getMaxSteps());

        $this->check(MapReader::TYPE_SOURCE);
        $this->check(MapReader::TYPE_DEST);

        $this->progress->finish();
        $this->displayErrors();
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
        $sourceDocuments = $this->getDocumentListWithoutPrefix($source->getDocumentList(), MapReader::TYPE_SOURCE);
        $destDocuments = array_flip(
            $this->getDocumentListWithoutPrefix($destination->getDocumentList(), MapReader::TYPE_DEST)
        );
        foreach ($sourceDocuments as $document) {
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
     * Process missing entities
     *
     * @return $this
     */
    protected function displayErrors()
    {
        if (isset($this->missingDocuments['source'])) {
            $this->logger->error(sprintf(
                PHP_EOL . 'Next documents from source are not mapped:%s',
                PHP_EOL . implode(',', array_keys($this->missingDocuments['source']))
            ));
        }
        if (isset($this->missingDocuments['destination'])) {
            $this->logger->error(sprintf(
                PHP_EOL . 'Next documents from destination are not mapped:%s',
                PHP_EOL . implode(',', array_keys($this->missingDocuments['destination']))
            ));
        }
        $errorMsgFields = '';
        if (isset($this->missingDocumentFields['source'])) {
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
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxSteps()
    {
        return count($this->source->getDocumentList()) + count($this->destination->getDocumentList());
    }

    /**
     * @param array $documentList
     * @param string $type - allowed values: MapReader::TYPE_SOURCE, MapReader::TYPE_DEST
     * @return mixed
     */
    protected function getDocumentListWithoutPrefix($documentList, $type)
    {
        $prefixType = $type == MapReader::TYPE_SOURCE ? 'source_prefix' : 'dest_prefix';
        $prefix = $this->config->getOption($prefixType);
        if (isset($prefix)) {
            foreach ($documentList as $documentKey => $documentValue) {
                $documentList[$documentKey] = preg_replace('/^' . $prefix . '/', '', $documentValue);
            }
        }
        return $documentList;
    }
}
