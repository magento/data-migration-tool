<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\App\Step;

use Migration\Logger\Logger;
use Migration\App\ProgressBar;
use Migration\Reader\MapFactory;
use Migration\Reader\MapInterface;
use Migration\ResourceModel;
use Migration\Config;

/**
 * Class AbstractIntegrity
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class AbstractIntegrity implements StageInterface
{
    /**
     * ResourceModel of source
     *
     * @var ResourceModel\Source
     */
    protected $source;

    /**
     * ResourceModel of destination
     *
     * @var ResourceModel\Destination
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
     * Not mapped documents
     *
     * @var array
     */
    protected $notMappedDocuments;

    /**
     * Not mapped document fields
     *
     * @var array
     */
    protected $notMappedDocumentFields;

    /**
     * Mismatch in data type of fields
     *
     * @var array
     */
    protected $mismatchDocumentFieldDataTypes;

    /**
     * Incompatible data in field of document
     *
     * @var array
     */
    protected $incompatibleDocumentFieldsData;

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
     * @var Config
     */
    protected $config;

    /**
     * @param ProgressBar\LogLevelProcessor $progress
     * @param Logger $logger
     * @param Config $config
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     * @param MapFactory $mapFactory
     * @param string $mapConfigOption
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progress,
        Logger $logger,
        Config $config,
        ResourceModel\Source $source,
        ResourceModel\Destination $destination,
        MapFactory $mapFactory,
        $mapConfigOption
    ) {
        $this->logger = $logger;
        $this->config = $config;
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
     * @param bool $verifyFields
     * @return $this
     */
    protected function check($documents, $type, $verifyFields = true)
    {
        $documents = $this->filterIgnoredDocuments($documents, $type);
        if (!empty($documents)) {
            $source = $type == MapInterface::TYPE_SOURCE ? $this->source : $this->destination;
            $destination = $type == MapInterface::TYPE_SOURCE ? $this->destination : $this->source;
            $destDocuments = array_flip($destination->getDocumentList());

            foreach ($documents as $sourceDocumentName) {
                $this->progress->advance();
                $destinationDocumentName = $this->getMappedDocumentName($sourceDocumentName, $type);

                $sourceDocument = $source->getDocument($sourceDocumentName);
                $destinationDocument = $destination->getDocument($destinationDocumentName);

                if (!isset($destDocuments[$destinationDocumentName]) || !$sourceDocument || !$destinationDocument) {
                    $this->notMappedDocuments[$type][$sourceDocumentName] = true;
                } else {
                    if ($verifyFields) {
                        $this->verifyFields($sourceDocument, $destinationDocument, $type);
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Get mapped document name
     *
     * @param string $documentName
     * @param string $type
     * @return mixed
     */
    protected function getMappedDocumentName($documentName, $type)
    {
        return $this->map->getDocumentMap($documentName, $type);
    }

    /**
     * Filter ignored documents
     *
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
     * Verify fields
     *
     * @param ResourceModel\Document $sourceDocument
     * @param ResourceModel\Document $destinationDocument
     * @param string $type
     * @return void
     */
    protected function verifyFields($sourceDocument, $destinationDocument, $type)
    {
        $sourceFields = $sourceDocument->getStructure()->getFields();
        $destFields = $destinationDocument->getStructure()->getFields();
        foreach ($sourceFields as $sourceField => $sourceFieldMetaData) {
            $mappedField = $this->map->getFieldMap($sourceDocument->getName(), $sourceField, $type);
            if ($mappedField) {
                if (!isset($destFields[$mappedField])) {
                    $this->notMappedDocumentFields[$type][$sourceDocument->getName()][] = $mappedField;
                } else if ($sourceFieldMetaData['DATA_TYPE'] != $destFields[$mappedField]['DATA_TYPE']
                    && !$this->map->isFieldDataTypeIgnored($sourceDocument->getName(), $sourceField, $type)
                ) {
                    $this->mismatchDocumentFieldDataTypes[$type][$sourceDocument->getName()][] = $sourceField;
                }
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
        $checkDocuments = $this->checkDocuments();
        $checkDocumentFields = $this->checkDocumentFields();
        $checkMismatchDocumentFieldDataTypes = $this->checkMismatchDocumentFieldDataTypes();
        $checkDocumentFieldsData = $this->checkDocumentFieldsData();
        return
            $checkDocuments
            && $checkDocumentFields
            && $checkMismatchDocumentFieldDataTypes
            && $checkDocumentFieldsData;
    }

    /**
     * Check documents
     *
     * @return bool
     */
    protected function checkDocuments()
    {
        $check = function ($data, $messagePattern, $type) {
            $isSuccess = true;
            if (isset($data[$type])) {
                $logLevel = $this->config->getOption(Config::OPTION_AUTO_RESOLVE) ? Logger::WARNING : Logger::ERROR;
                $isSuccess = false;
                $this->logger->addRecord($logLevel, sprintf(
                    $messagePattern,
                    implode(',', array_keys($data[$type]))
                ));
            }
            return $isSuccess;
        };
        $missingDocumentsSource = $check(
            $this->missingDocuments,
            'Source documents are missing: %s',
            MapInterface::TYPE_SOURCE
        );
        $missingDocumentsDestination = $check(
            $this->missingDocuments,
            'Destination documents are missing: %s',
            MapInterface::TYPE_DEST
        );
        $notMappedDocumentsSource = $check(
            $this->notMappedDocuments,
            'Source documents are not mapped: %s',
            MapInterface::TYPE_SOURCE
        );
        $notMappedDocumentsDestination = $check(
            $this->notMappedDocuments,
            'Destination documents are not mapped: %s',
            MapInterface::TYPE_DEST
        );

        return $missingDocumentsSource
            && $missingDocumentsDestination
            && $notMappedDocumentsSource
            && $notMappedDocumentsDestination;
    }

    /**
     * Check fields of document
     *
     * @return bool
     */
    protected function checkDocumentFields()
    {
        $check = function ($data, $messagePattern, $type) {
            $isSuccess = true;
            if (isset($data[$type])) {
                $logLevel = $this->config->getOption(Config::OPTION_AUTO_RESOLVE) ? Logger::WARNING : Logger::ERROR;
                $isSuccess = false;
                foreach ($data[$type] as $document => $fields) {
                    $this->logger->addRecord($logLevel, sprintf(
                        $messagePattern,
                        $document,
                        implode(',', $fields)
                    ));
                }
            }
            return $isSuccess;
        };
        $missingDocumentsSource = $check(
            $this->missingDocumentFields,
            'Source fields are missing. Document: %s. Fields: %s',
            MapInterface::TYPE_SOURCE
        );
        $missingDocumentsDestination = $check(
            $this->missingDocumentFields,
            'Destination fields are missing. Document: %s. Fields: %s',
            MapInterface::TYPE_DEST
        );
        $notMappedDocumentsSource = $check(
            $this->notMappedDocumentFields,
            'Source fields are not mapped. Document: %s. Fields: %s',
            MapInterface::TYPE_SOURCE
        );
        $notMappedDocumentsDestination = $check(
            $this->notMappedDocumentFields,
            'Destination fields are not mapped. Document: %s. Fields: %s',
            MapInterface::TYPE_DEST
        );

        return $missingDocumentsSource
            && $missingDocumentsDestination
            && $notMappedDocumentsSource
            && $notMappedDocumentsDestination;
    }

    /**
     * Check mismatch in data types of document field
     *
     * @return bool
     */
    protected function checkMismatchDocumentFieldDataTypes()
    {
        if (isset($this->mismatchDocumentFieldDataTypes[MapInterface::TYPE_SOURCE])) {
            foreach ($this->mismatchDocumentFieldDataTypes[MapInterface::TYPE_SOURCE] as $document => $fields) {
                $this->logger->warning(sprintf(
                    'Mismatch of data types. Source document: %s. Fields: %s',
                    $document,
                    implode(',', $fields)
                ));
            }
        }
        if (isset($this->mismatchDocumentFieldDataTypes[MapInterface::TYPE_DEST])) {
            foreach ($this->mismatchDocumentFieldDataTypes[MapInterface::TYPE_DEST] as $document => $fields) {
                $this->logger->warning(sprintf(
                    'Mismatch of data types. Destination document: %s. Fields: %s',
                    $document,
                    implode(',', $fields)
                ));
            }
        }
        return true;
    }

    /**
     * Check data in document fields
     *
     * @return bool
     */
    protected function checkDocumentFieldsData()
    {
        $isSuccess = true;
        $logLevel = $this->config->getOption(Config::OPTION_AUTO_RESOLVE) ? Logger::WARNING : Logger::ERROR;
        if (isset($this->incompatibleDocumentFieldsData[MapInterface::TYPE_SOURCE])) {
            foreach ($this->incompatibleDocumentFieldsData[MapInterface::TYPE_SOURCE] as $errorDetail) {
                $this->logger->addRecord($logLevel, sprintf(
                    'Incompatibility in data. Source document: %s. Field: %s. Error: %s',
                    $errorDetail['document'],
                    $errorDetail['field'],
                    $errorDetail['error']
                ));
                $isSuccess = false;
            }
        }
        if (isset($this->incompatibleDocumentFieldsData[MapInterface::TYPE_DEST])) {
            foreach ($this->incompatibleDocumentFieldsData[MapInterface::TYPE_DEST] as $errorDetail) {
                $this->logger->addRecord($logLevel, sprintf(
                    'Incompatibility in data. Destination document: %s. Field: %s. Error: %s',
                    $errorDetail['document'],
                    $errorDetail['field'],
                    $errorDetail['error']
                ));
                $isSuccess = false;
            }
        }
        return $isSuccess;
    }
}
