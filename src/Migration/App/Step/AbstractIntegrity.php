<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\App\Step;

use Migration\Logger\Logger;
use Migration\App\ProgressBar;
use Migration\Reader\MapFactory;
use Migration\Reader\MapInterface;
use Migration\Resource;
use Migration\Logger\Manager as LogManager;

/**
 * Class AbstractIntegrity
 */
abstract class AbstractIntegrity implements StageInterface
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
     * @var \Migration\Reader\MapInterface
     */
    protected $map;

    /**
     * LogLevelProcessor instance
     *
     * @var ProgressBar\LogLevelProcessor
     */
    protected $progress;

    /**
     * @var bool
     */
    protected $hasMappedDocuments = true;

    /**
     * @param ProgressBar\LogLevelProcessor $progress
     * @param Logger $logger
     * @param Resource\Source $source
     * @param Resource\Destination $destination
     * @param MapFactory $mapFactory
     * @param string $mapConfigOption
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progress,
        Logger $logger,
        Resource\Source $source,
        Resource\Destination $destination,
        MapFactory $mapFactory,
        $mapConfigOption
    ) {
        $this->logger = $logger;
        $this->progress = $progress;
        $this->source = $source;
        $this->destination = $destination;
        $this->map = $mapFactory->create($mapConfigOption);
    }

    /**
     * Returns number of iterations for integrity check
     *
     * @return mixed
     */
    abstract protected function getIterationsCount();

    /**
     * Check if source and destination resources have equal document names and fields
     *
     * @param array $documents
     * @param string $type - allowed values: MapInterface::TYPE_SOURCE, MapInterface::TYPE_DEST
     * @return $this
     * @throws \Exception
     */
    protected function check($documents, $type)
    {
        $documents = $this->filterIgnoredDocuments($documents, $type);
        if (!empty($documents)) {
            $this->hasMappedDocuments = false;

            $source = $type == MapInterface::TYPE_SOURCE ? $this->source : $this->destination;
            $destination = $type == MapInterface::TYPE_SOURCE ? $this->destination : $this->source;
            $destDocuments = array_flip($destination->getDocumentList());

            foreach ($documents as $sourceDocumentName) {
                $this->progress->advance();
                $destinationDocumentName = $this->map->getDocumentMap($sourceDocumentName, $type);

                $sourceDocument = $source->getDocument($sourceDocumentName);
                $destinationDocument = $destination->getDocument($destinationDocumentName);

                if (!isset($destDocuments[$destinationDocumentName]) || !$sourceDocument || !$destinationDocument) {
                    $this->missingDocuments[$type][$sourceDocumentName] = true;
                } else {
                    $this->hasMappedDocuments = true;
                    $this->verifyFields($sourceDocument, $destinationDocument, $type);
                }
            }
        }
        return $this;
    }

    /**
     * @param array $documents
     * @param string $type
     * @return array
     */
    protected function filterIgnoredDocuments($documents, $type)
    {
        $result = [];
        foreach ($documents as $document) {
            if (!$this->map->isDocumentIgnored($document, $type)) {
                $result[] = $document;
            }
        }
        return $result;
    }

    /**
     * @param Resource\Document $sourceDocument
     * @param Resource\Document $destinationDocument
     * @param string $type
     * @return void
     */
    protected function verifyFields($sourceDocument, $destinationDocument, $type)
    {
        $sourceFields = array_keys($sourceDocument->getStructure()->getFields());
        $destFields = $destinationDocument->getStructure()->getFields();
        foreach ($sourceFields as $field) {
            $mappedField = $this->map->getFieldMap($sourceDocument->getName(), $field, $type);
            if ($mappedField && !isset($destFields[$mappedField])) {
                $this->missingDocumentFields[$type][$sourceDocument->getName()][] = $mappedField;
            }
        }
    }

    /**
     * Process missing entities and log them in to the file
     *
     * @return bool
     */
    protected function checkForErrors()
    {
        $isSuccess = true;
        if (!$this->hasMappedDocuments) {
            $this->logger->error('Mapped documents not found. Check your configuration.');
            return false;
        }

        if (isset($this->missingDocuments[MapInterface::TYPE_SOURCE])) {
            $isSuccess = false;
            $this->logger->error(sprintf(
                'Next documents from source are not mapped:%s',
                PHP_EOL . implode(',', array_keys($this->missingDocuments[MapInterface::TYPE_SOURCE]))
            ));
        }
        if (isset($this->missingDocuments[MapInterface::TYPE_DEST])) {
            $isSuccess = false;
            $this->logger->error(sprintf(
                'Next documents from destination are not mapped:%s',
                PHP_EOL . implode(',', array_keys($this->missingDocuments[MapInterface::TYPE_DEST]))
            ));
        }
        $errorMsgFields = '';
        if (isset($this->missingDocumentFields[MapInterface::TYPE_SOURCE])) {
            $isSuccess = false;
            foreach ($this->missingDocumentFields[MapInterface::TYPE_SOURCE] as $document => $fields) {
                $errorMsgFields .= sprintf(
                    'Document name: %s; Fields: %s',
                    $document,
                    implode(',', $fields)
                );
            }
            $this->logger->error('Next fields from source are not mapped:' . $errorMsgFields);
        }
        $errorMsgFields = '';
        if (isset($this->missingDocumentFields[MapInterface::TYPE_DEST])) {
            $isSuccess = false;
            foreach ($this->missingDocumentFields[MapInterface::TYPE_DEST] as $document => $fields) {
                $errorMsgFields .= sprintf(
                    'Document name: %s; Fields: %s',
                    $document,
                    implode(',', $fields)
                );
            }
            $this->logger->error(
                'Next fields from destination are not mapped:' . $errorMsgFields
            );
        }
        return $isSuccess;
    }
}
