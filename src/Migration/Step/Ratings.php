<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

use Migration\Logger\Logger;
use Migration\App\ProgressBar;
use Migration\Resource\Destination;

/**
 * Class Ratings
 */
class Ratings extends DatabaseStage
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
     * @var string
     */
    protected $stage;

    /**
     * @param Destination $destination
     * @param Logger $logger
     * @param ProgressBar\LogLevelProcessor $progress
     * @param string $stage
     */
    public function __construct(
        Destination $destination,
        Logger $logger,
        ProgressBar\LogLevelProcessor $progress,
        $stage
    ) {
        $this->destination = $destination;
        $this->logger = $logger;
        $this->progress = $progress;
        $this->stage = $stage;
    }

    /**
     * @return bool
     */
    protected function integrity()
    {
        $this->progress->start(1);
        $this->progress->advance();
        $documents = $this->destination->getDocumentList();
        if (!in_array(self::RATING_TABLE_NAME, $documents)
            || !in_array(self::RATING_STORE_TABLE_NAME, $documents)
        ) {
            $this->logger->error(
                sprintf(
                    'Integrity check failed due to "%s" or "%s" documents do not exist in the destination resource',
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
                    'Integrity check failed due to "is_active" field does not exist in "%s" document of'
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
     * @return bool
     */
    protected function data()
    {
        $this->progress->start(1);
        $this->progress->advance();
        $ratingsIsActive = [];
        /** @var \Migration\Resource\Adapter\Mysql $adapter */
        $adapter = $this->destination->getAdapter();
        /** @var \Magento\Framework\DB\Select $select */
        $select = $adapter->getSelect()->from($this->getRatingStoreDocument(), ['rating_id'])->where('store_id > 0');
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
        return true;
    }

    /**
     * @inheritdoc
     */
    public function perform()
    {
        if (!method_exists($this, $this->stage)) {
            throw new \Migration\Exception('Invalid step configuration');
        }

        return call_user_func([$this, $this->stage]);
    }

    /**
     * @return bool
     */
    protected function volume()
    {
        $result = true;
        $this->progress->start(1);
        $this->progress->advance();
        $ratingsShouldBeActive = [];
        $ratingsIsActive = [];
        /** @var \Migration\Resource\Adapter\Mysql $adapter */
        $adapter = $this->destination->getAdapter();
        /** @var \Magento\Framework\DB\Select $select */
        $select = $adapter->getSelect()->from($this->getRatingStoreDocument(), ['rating_id'])->where('store_id > 0');
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
            $this->logger->warning(
                sprintf(
                    'Mismatch of entities in the documents: %s, %s',
                    self::RATING_TABLE_NAME,
                    self::RATING_STORE_TABLE_NAME
                )
            );
            $result = false;
        }
        $this->progress->finish();
        return $result;
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
