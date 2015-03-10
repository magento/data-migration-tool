<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

use Migration\Logger\Logger;
use Migration\ProgressBar;
use Migration\Resource\Destination;

/**
 * Class Ratings
 */
class Ratings extends DatabaseStep
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
     * @var ProgressBar
     */
    protected $progress;

    /**
     * @param Destination $destination
     * @param Logger $logger
     * @param ProgressBar $progress
     */
    public function __construct(
        Destination $destination,
        Logger $logger,
        ProgressBar $progress
    ) {
        $this->destination = $destination;
        $this->logger = $logger;
        $this->progress = $progress;
    }

    /**
     * @inheritdoc
     */
    public function integrity()
    {
        $this->progress->start(1);
        $this->progress->advance();
        $documents = $this->destination->getDocumentList();
        if (!in_array($this->getRatingDocument(), $documents)
            || !in_array($this->getRatingStoreDocument(), $documents)
        ) {
            $this->logger->error(
                sprintf(
                    'Integrity check failed due to "%s" or "%s" documents are not exist in the destination resource',
                    $this->getRatingDocument(),
                    $this->getRatingStoreDocument()
                )
            );
            return false;
        }
        $structureRating = $this->destination->getDocument($this->getRatingDocument())->getStructure()->getFields();
        if (!array_key_exists('is_active', $structureRating)) {
            $this->logger->error(
                'Integrity check failed due to is_active field is not exists in the destination resource'
            );
            return false;
        }
        $this->progress->finish();
        return true;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->progress->start(1);
        $this->progress->advance();
        $ratingsIsActive = [];
        $adapter = $this->destination->getAdapter();
        /** @var \Magento\Framework\DB\Select $select */
        $select = $adapter->getSelect()->from($this->getRatingStoreDocument(), ['rating_id']);
        $ratingsStore = $adapter->loadDataFromSelect($select);
        foreach ($ratingsStore as $rating) {
            $ratingsIsActive[] = $rating['rating_id'];
        }
        $ratingsIsActive = array_unique($ratingsIsActive);
        if ($ratingsIsActive) {
            $adapter->updateDocument(
                $this->getRatingDocument(),
                ['is_active' => 1],
                sprintf('rating_id IN (%s)', implode(',', $ratingsIsActive))
            );
        }
        $this->progress->finish();
    }

    /**
     * @inheritdoc
     */
    public function volumeCheck()
    {
        $this->progress->start(1);
        $this->progress->advance();
        $ratingsShouldBeActive = [];
        $ratingsIsActive = [];
        $adapter = $this->destination->getAdapter();
        /** @var \Magento\Framework\DB\Select $select */
        $select = $adapter->getSelect()->from($this->getRatingStoreDocument(), ['rating_id']);
        $ratingsStore = $adapter->loadDataFromSelect($select);
        foreach ($ratingsStore as $rating) {
            $ratingsShouldBeActive[] = $rating['rating_id'];
        }
        $ratingsShouldBeActive = array_unique($ratingsShouldBeActive);

        /** @var \Magento\Framework\DB\Select $select */
        $select = $adapter->getSelect()
            ->from($this->getRatingDocument(), ['rating_id'])
            ->where('is_active = ?', 1);
        $ratings = $adapter->loadDataFromSelect($select);
        foreach ($ratings as $rating) {
            $ratingsIsActive[] = $rating['rating_id'];
        }
        if (count(array_intersect($ratingsShouldBeActive, $ratingsIsActive)) != count($ratingsShouldBeActive)) {
            $this->logger->error(
                sprintf(
                    'Volume check failed due to discrepancy in "%s" and "%s" documents in the destination resource',
                    $this->getRatingDocument(),
                    $this->getRatingStoreDocument()
                )
            );
            return false;
        }
        $this->progress->finish();
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return 'Ratings step';
    }

    /**
     * Returns rating document name
     *
     * @return string
     */
    protected function getRatingDocument()
    {
        return $this->destination->addDocumentPrefix(self::RATING_TABLE_NAME);
    }

    /**
     * Returns rating store document name
     *
     * @return string
     */
    protected function getRatingStoreDocument()
    {
        return $this->destination->addDocumentPrefix(self::RATING_STORE_TABLE_NAME);
    }
}
