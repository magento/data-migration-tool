<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Ratings;

use Migration\App\ProgressBar;
use Migration\ResourceModel\Destination;
use Migration\App\Step\StageInterface;

/**
 * Class Data
 */
class Data implements StageInterface
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
     * @param ProgressBar\LogLevelProcessor $progress
     */
    public function __construct(
        Destination $destination,
        ProgressBar\LogLevelProcessor $progress
    ) {
        $this->destination = $destination;
        $this->progress = $progress;
    }

    /**
     * @inheritdoc
     */
    public function perform()
    {
        $this->progress->start(1);
        $this->progress->advance();
        $ratingsIsActive = [];
        /** @var \Migration\ResourceModel\Adapter\Mysql $adapter */
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
