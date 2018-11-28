<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Ratings;

use Migration\Logger\Logger;
use Migration\App\ProgressBar;
use Migration\ResourceModel\Destination;
use Migration\App\Step\AbstractVolume;

/**
 * Class Volume
 */
class Volume extends AbstractVolume
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
        $this->progress = $progress;
        parent::__construct($logger);
    }

    /**
     * @inheritdoc
     */
    public function perform()
    {
        $this->progress->start(1);
        $this->progress->advance();
        $ratingsShouldBeActive = [];
        $ratingsIsActive = [];
        /** @var \Migration\ResourceModel\Adapter\Mysql $adapter */
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
            $this->errors[] = sprintf(
                'Mismatch of entities in the documents: %s, %s',
                self::RATING_TABLE_NAME,
                self::RATING_STORE_TABLE_NAME
            );
        }
        $this->progress->finish();
        return $this->checkForErrors(Logger::ERROR);
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
