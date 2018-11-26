<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Ratings;

use Migration\Logger\Logger;
use Migration\App\ProgressBar;
use Migration\ResourceModel\Destination;

/**
 * Class Integrity
 */
class Integrity extends \Migration\App\Step\AbstractIntegrity
{
    const RATING_TABLE_NAME = 'rating';
    const RATING_STORE_TABLE_NAME = 'rating_store';

    /**
     * Destination resource
     *
     * @var Destination
     */
    protected $destination;

    /**
     * Logger instance
     *
     * @var Logger
     */
    protected $logger;

    /**
     * Progress bar
     *
     * @var ProgressBar\LogLevelProcessor
     */
    protected $progress;

    /**
     * @param Destination $destination
     * @param Logger $logger
     * @param ProgressBar\LogLevelProcessor $progress
     */
    public function __construct(
        Destination $destination,
        Logger $logger,
        ProgressBar\LogLevelProcessor $progress
    ) {
        $this->destination = $destination;
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
        $documents = $this->destination->getDocumentList();
        if (!in_array(self::RATING_TABLE_NAME, $documents)
            || !in_array(self::RATING_STORE_TABLE_NAME, $documents)
        ) {
            $this->logger->error(
                sprintf(
                    '"%s" or "%s" documents do not exist in the destination resource',
                    self::RATING_TABLE_NAME,
                    self::RATING_STORE_TABLE_NAME
                )
            );
            return false;
        }

        $structureRating = $this->destination->getDocument(self::RATING_TABLE_NAME)->getStructure()->getFields();
        if (!array_key_exists('is_active', $structureRating)) {
            $this->logger->error(
                sprintf(
                    '"is_active" field does not exist in "%s" document of'
                    . ' the destination resource',
                    self::RATING_TABLE_NAME
                )
            );
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
