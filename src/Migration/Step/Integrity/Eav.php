<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Integrity;

use Migration\Logger\Logger;
use Migration\MapReader;
use Migration\Resource;
use Migration\Step\Eav\Helper;

/**
 * Class Eav
 */
class Eav extends AbstractIntegrity
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param Logger $logger
     * @param Resource\Source $source
     * @param Resource\Destination $destination
     * @param MapReader $mapReader
     * @param Helper $helper
     */
    public function __construct(
        Logger $logger,
        Resource\Source $source,
        Resource\Destination $destination,
        MapReader $mapReader,
        Helper $helper
    ) {
        parent::__construct($logger, $source, $destination, $mapReader);
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function perform()
    {
        $this->check(array_keys($this->helper->getDocumentsMap()), MapReader::TYPE_SOURCE);
        $this->check(array_values($this->helper->getDocumentsMap()), MapReader::TYPE_DEST);
        return $this->checkForErrors();
    }
}
