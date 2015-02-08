<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

use Migration\Logger\Logger;
use Migration\MapReader;
use Migration\Resource;

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
     * @var $this
     */
    protected $map;

    /**
     * @param Progress $progress
     * @param Logger $logger
     * @param Resource\Source $source
     * @param Resource\Destination $destination
     * @param MapReader $mapReader
     */
    public function __construct(
        Progress $progress,
        Logger $logger,
        Resource\Source $source,
        Resource\Destination $destination,
        MapReader $mapReader
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->map = $mapReader;
        parent::__construct($progress, $logger);
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        parent::run();

        $this->check(MapReader::TYPE_SOURCE);
        $this->check(MapReader::TYPE_DEST);
        $this->processMissingEntities();

        if (!$this->checkMismatch()) {
            $this->progress->finish();
        } else {
            $this->progress->fail();
        }
    }

    /**
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
     * @return bool
     */
    protected function processMissingEntities()
    {
        if (isset($this->missingDocuments['source'])) {
            $this->logger->error(sprintf(
                "Next documents from source are not mapped:\n%s\n",
                implode(',', array_keys($this->missingDocuments['source']))
            ));
        }
        if (isset($this->missingDocuments['destination'])) {
            $this->logger->error(sprintf(
                "Next documents from destination are not mapped:\n%s\n",
                implode(',', array_keys($this->missingDocuments['destination']))
            ));
        }
        $errorMsgFields = '';
        if (isset($this->missingDocumentFields['source'])) {
            foreach ($this->missingDocumentFields['source'] as $document => $fields) {
                $errorMsgFields .= sprintf(
                    "Document name:%s; Fields:%s\n",
                    $document,
                    implode(',', $fields)
                );
            }
            $this->logger->error(
                "Next fields from source are not mapped:\n{$errorMsgFields}"
            );
        }
        $errorMsgFields = '';
        if (isset($this->missingDocumentFields['destination'])) {
            foreach ($this->missingDocumentFields['destination'] as $document => $fields) {
                $errorMsgFields .= sprintf(
                    "Document name:%s; Fields:%s\n",
                    $document,
                    implode(',', $fields)
                );
            }
            $this->logger->error(
                "Next fields from destination are not mapped:\n{$errorMsgFields}"
            );
        }
    }

    /**
     * Check mismatch
     *
     * @return bool
     */
    protected function checkMismatch()
    {
        $hasErrors = false;
        if (!empty($this->missingDocuments['source'])
            || !empty($this->missingDocuments['destination'])
            || !empty($this->missingDocumentFields['source'])
            || !empty($this->missingDocumentFields['destination'])
        ) {
            $hasErrors = true;
            $this->progress->fail();
        }
        return $hasErrors;
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxSteps()
    {
        return count($this->source->getDocumentList()) + count($this->destination->getDocumentList());
    }
}
