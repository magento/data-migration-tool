<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Settings;

use Migration\App\Step\StageInterface;
use Migration\Reader\Settings as ReaderSettings;
use Migration\Logger\Logger;
use Migration\App\ProgressBar;
use Migration\ResourceModel\Destination;
use Migration\ResourceModel\Source;
use Migration\ResourceModel;
use Migration\ResourceModel\Document;
use Migration\ResourceModel\Record;
use Migration\Handler;

/**
 * Class Integrity
 */
class Integrity extends \Migration\App\Step\AbstractIntegrity
{
    const CONFIG_TABLE_NAME_SOURCE = 'core_config_data';
    const CONFIG_TABLE_NAME_DESTINATION = 'core_config_data';
    const CONFIG_FIELD_CONFIG_ID = 'config_id';
    const CONFIG_FIELD_SCOPE_ID = 'scope_id';
    const CONFIG_FIELD_SCOPE = 'scope';
    const CONFIG_FIELD_PATH = 'path';
    const CONFIG_FIELD_VALUE = 'value';

    /**
     * @var Destination
     */
    protected $destination;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Source
     */
    protected $source;

    /**
     * @var ProgressBar\LogLevelProcessor
     */
    protected $progress;

    /**
     * @param Destination $destination
     * @param Source $source
     * @param Logger $logger
     * @param ProgressBar\LogLevelProcessor $progress
     */
    public function __construct(
        Destination $destination,
        Source $source,
        Logger $logger,
        ProgressBar\LogLevelProcessor $progress
    ) {
        $this->destination = $destination;
        $this->source = $source;
        $this->logger = $logger;
        $this->progress = $progress;
    }

    /**
     * @inheritdoc
     */
    public function perform()
    {
        $this->progress->start(1);
        $this->progress->advance();
        $documents = $this->source->getDocumentList();
        if (!in_array(self::CONFIG_TABLE_NAME_SOURCE, $documents)) {
            $this->logger->error(
                sprintf(
                    'Integrity check failed due to "%s" document does not exist in the source resource',
                    self::CONFIG_TABLE_NAME_SOURCE
                )
            );
            $this->logger->notice('Please check if table names uses prefix, add it to your config.xml file');
            return false;
        }
        $documents = $this->destination->getDocumentList();
        if (!in_array(self::CONFIG_TABLE_NAME_DESTINATION, $documents)) {
            $this->logger->error(
                sprintf(
                    'Integrity check failed due to "%s" document does not exist in the destination resource',
                    self::CONFIG_TABLE_NAME_DESTINATION
                )
            );
            $this->logger->notice('Please check if table names uses prefix, add it to your config.xml file');
            return false;
        }
        $this->progress->finish();
        return true;
    }

    /**
     * @inheritdoc
     */
    protected function getIterationsCount()
    {
        return 0;
    }
}
