<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\SalesOrder;

use Migration\Logger\Logger;
use Migration\Reader\MapFactory;
use Migration\Reader\MapInterface;
use Migration\ResourceModel;
use Migration\App\ProgressBar;
use Migration\Config;

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
     * @param ProgressBar\LogLevelProcessor $progress
     * @param Logger $logger
     * @param Config $config
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     * @param MapFactory $mapFactory
     * @param Helper $helper
     * @param string $mapConfigOption
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progress,
        Logger $logger,
        Config $config,
        ResourceModel\Source $source,
        ResourceModel\Destination $destination,
        MapFactory $mapFactory,
        Helper $helper,
        $mapConfigOption = 'sales_order_map_file'
    ) {
        parent::__construct($progress, $logger, $config, $source, $destination, $mapFactory, $mapConfigOption);
        $this->helper = $helper;
    }

    /**
     * @inheritdoc
     */
    public function perform()
    {
        $this->progress->start($this->getIterationsCount());
        $this->check(array_keys($this->helper->getDocumentList()), MapInterface::TYPE_SOURCE);
        $this->check(array_values($this->helper->getDocumentList()), MapInterface::TYPE_DEST);
        $this->checkEavEntities();
        $this->progress->finish();
        return $this->checkForErrors();
    }

    /**
     * Check eav entities
     *
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
     * Get eav entities
     *
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
     * Get iterations count
     *
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
