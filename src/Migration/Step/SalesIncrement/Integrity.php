<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\SalesIncrement;

use Migration\Reader\MapInterface;
use Migration\Logger\Logger;
use Migration\App\ProgressBar;
use Migration\ResourceModel\Destination;

/**
 * Class Integrity
 */
class Integrity extends \Migration\App\Step\AbstractIntegrity
{
    /**
     * @var Destination
     */
    protected $destination;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var ProgressBar\LogLevelProcessor
     */
    protected $progress;

    /**
     * @param Destination $destination
     * @param Logger $logger
     * @param ProgressBar\LogLevelProcessor $progress
     * @param Helper $helper
     */
    public function __construct(
        Destination $destination,
        Logger $logger,
        ProgressBar\LogLevelProcessor $progress,
        Helper $helper
    ) {
        $this->destination = $destination;
        $this->logger = $logger;
        $this->progress = $progress;
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function perform()
    {
        $this->progress->start(1);
        $this->progress->advance();

        if (!$this->destination->getDocument($this->helper->getSequenceProfileTable())) {
            $this->missingDocuments[MapInterface::TYPE_DEST][$this->helper->getSequenceProfileTable()] = true;
        } else {
            $structureExistingSequenceProfileTable = array_keys(
                $this->destination
                ->getDocument($this->helper->getSequenceProfileTable())
                ->getStructure()
                ->getFields()
            );
            $this->checkStructure(
                $this->helper->getSequenceProfileTable(),
                $this->helper->getSequenceProfileTable(true),
                $structureExistingSequenceProfileTable
            );
        }
        if (!$this->destination->getDocument($this->helper->getSequenceMetaTable())) {
            $this->missingDocuments[MapInterface::TYPE_DEST][$this->helper->getSequenceMetaTable()] = true;
        } else {
            $structureExistingSequenceMetaTable = array_keys(
                $this->destination
                ->getDocument($this->helper->getSequenceMetaTable())
                ->getStructure()
                ->getFields()
            );
            $this->checkStructure(
                $this->helper->getSequenceMetaTable(),
                $this->helper->getSequenceMetaTable(true),
                $structureExistingSequenceMetaTable
            );
        }
        $this->progress->finish();
        return $this->checkForErrors();
    }

    /**
     * @param string $documentName
     * @param array $source
     * @param array $destination
     * @return void
     */
    protected function checkStructure($documentName, array $source, array $destination)
    {
        $fieldsDiff = array_diff($source, $destination);
        if ($fieldsDiff) {
            $this->missingDocumentFields[MapInterface::TYPE_DEST][$documentName] = $fieldsDiff;
        }
        $fieldsDiff = array_diff($destination, $source);
        if ($fieldsDiff) {
            $this->missingDocumentFields[MapInterface::TYPE_SOURCE][$documentName] = $fieldsDiff;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function checkForErrors()
    {
        $checkMissingDocuments = $this->checkMissingDocuments();
        $checkMissingDocumentFields = $this->checkMissingDocumentFields();
        return $checkMissingDocuments && $checkMissingDocumentFields;
    }

    /**
     * {@inheritdoc}
     */
    protected function getIterationsCount()
    {
        return 0;
    }
}
