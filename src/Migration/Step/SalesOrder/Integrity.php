<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\SalesOrder;

use Migration\Logger\Logger;
use Migration\MapReader\MapReaderSalesOrder;
use Migration\MapReaderInterface;
use Migration\Resource;
use Migration\ProgressBar;

/**
 * Class Integrity
 */
class Integrity extends \Migration\App\Step\AbstractIntegrity
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param ProgressBar $progress
     * @param Logger $logger
     * @param Resource\Source $source
     * @param Resource\Destination $destination
     * @param MapReaderSalesOrder $mapReader
     * @param Helper $helper
     */
    public function __construct(
        ProgressBar $progress,
        Logger $logger,
        Resource\Source $source,
        Resource\Destination $destination,
        MapReaderSalesOrder $mapReader,
        Helper $helper
    ) {
        parent::__construct($progress, $logger, $source, $destination, $mapReader);
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function perform()
    {
        $this->progress->start($this->getIterationsCount());
        $this->check(array_keys($this->helper->getDocumentList()), MapReaderInterface::TYPE_SOURCE);
        $this->check(array_values($this->helper->getDocumentList()), MapReaderInterface::TYPE_DEST);
        $this->checkEavEntities();
        $this->progress->finish();
        return $this->checkForErrors();
    }

    /**
     * @return void
     */
    protected function checkEavEntities()
    {
        $this->progress->advance();
        $eavAttributes = $this->helper->getEavAttributes();
        $destEavEntities = $this->getEavEntities($eavAttributes);
        foreach ($eavAttributes as $field) {
            if (!in_array($field, $destEavEntities)) {
                $this->missingDocumentFields['destination']['eav_attribute'][] = $field;
            }
        }
    }

    /**
     * @param array $attributes
     * @return array
     */
    protected function getEavEntities($attributes)
    {
        $eavAttributesData = [];
        foreach ($attributes as $eavEntity) {
            $pageNumber = 0;
            while (!empty($bulk = $this->destination->getRecords('eav_attribute', $pageNumber))) {
                $pageNumber++;
                foreach ($bulk as $eavData) {
                    if ($eavData['attribute_code'] == $eavEntity) {
                        $eavAttributesData[] = $eavData['attribute_code'];
                        break;
                    }
                }
            }
        }
        return $eavAttributesData;
    }

    /**
     * @return int
     */
    protected function getIterationsCount()
    {
        $migrationDocuments = $this->helper->getDocumentList();
        $documents = [
            $this->helper->getDestEavDocument(),
            array_keys($migrationDocuments),
            array_values($migrationDocuments)
        ];
        return count($documents);
    }
}
