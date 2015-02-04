<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

use Migration\Logger\Logger;
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
     * Constructor
     *
     * @param Progress $progress
     * @param Logger $logger
     * @param Resource\Source $source
     * @param Resource\Destination $destination
     */
    public function __construct(
        Progress $progress,
        Logger $logger,
        Resource\Source $source,
        Resource\Destination $destination
    ) {
        $this->source = $source;
        $this->destination = $destination;
        parent::__construct($progress, $logger);
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        parent::run();
        $currentProgress = $this->progress->getProgress();
        $documentListSource = $this->source->getDocumentList();
        $documentListDestination = $this->destination->getDocumentList();
        $this->missingDocuments['source'] = array_diff($documentListSource, $documentListDestination);
        $this->missingDocuments['destination'] = array_diff($documentListDestination, $documentListSource);
        $this->checkMismatch();
        for ($i = $currentProgress; $i < $this->progress->getMaxSteps(); $i++) {
            $this->progress->advance();
            $this->checkMismatch();
            $documentSource = $this->source->getDocument($documentListSource[$i]);
            $documentDestination = $this->destination->getDocument($documentSource->getName());
            $this->logger->debug("Integrity check of {$documentSource->getName()}");
            if ($documentSource && $documentDestination) {
                $documentSourceStructure = array_keys($documentSource->getStructure()->getFields());
                $documentDestinationStructure = array_keys($documentDestination->getStructure()->getFields());
                $sourceFieldsNotExist = array_diff($documentSourceStructure, $documentDestinationStructure);
                $destinationFieldsNotExist = array_diff($documentDestinationStructure, $documentSourceStructure);
                if ($sourceFieldsNotExist) {
                    $this->missingDocumentFields['source'][$documentSource->getName()] = $sourceFieldsNotExist;
                }
                if ($destinationFieldsNotExist) {
                    $this->missingDocumentFields['destination'][$documentSource->getName()]
                        = $destinationFieldsNotExist;
                }
            }
        }
        $this->processMissingEntities();
        if (!$this->checkMismatch()) {
            $this->progress->finish();
        } else {
            $this->progress->fail();
        }
    }

    /**
     * Process missing entities
     *
     * @return bool
     */
    protected function processMissingEntities()
    {
        if (!empty($this->missingDocuments['source'])) {
            $this->logger->error(sprintf(
                "The documents bellow are not exist in the destination resource:\n%s\n",
                implode(',', $this->missingDocuments['source'])
            ));
        }
        if (!empty($this->missingDocuments['destination'])) {
            $this->logger->error(sprintf(
                "The documents bellow are not exist in the source resource:\n%s\n",
                implode(',', $this->missingDocuments['destination'])
            ));
        }
        $errorMsgFields = '';
        if (!empty($this->missingDocumentFields['source'])) {
            foreach ($this->missingDocumentFields['source'] as $document => $fields) {
                $errorMsgFields .= sprintf(
                    "Document name:%s; Fields:%s\n",
                    $document,
                    implode(',', $fields)
                );
            }
            $this->logger->error(
                "In the documents bellow fields are not exist in the destination resource:\n{$errorMsgFields}"
            );
        }
        $errorMsgFields = '';
        if (!empty($this->missingDocumentFields['destination'])) {
            foreach ($this->missingDocumentFields['destination'] as $document => $fields) {
                $errorMsgFields .= sprintf(
                    "Document name:%s; Fields:%s\n",
                    $document,
                    implode(',', $fields)
                );
            }
            $this->logger->error(
                "In the documents bellow fields are not exist in the source resource:\n{$errorMsgFields}"
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
        $errorFound = false;
        if (!empty($this->missingDocuments['source'])
            || !empty($this->missingDocuments['destination'])
            || !empty($this->missingDocumentFields['source'])
            || !empty($this->missingDocumentFields['destination'])
        ) {
            $errorFound = true;
            $this->progress->fail();
        }
        return $errorFound;
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxSteps()
    {
        return count($this->source->getDocumentList());
    }
}
