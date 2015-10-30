<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\TierPrice;

use Migration\App\Step\AbstractVolume;
use Migration\Resource;
use Migration\App\ProgressBar;
use Migration\Logger\Logger;

/**
 * Class Volume
 */
class Volume extends AbstractVolume
{
    /**
     * @var Resource\Source
     */
    protected $source;

    /**
     * @var Resource\Destination
     */
    protected $destination;

    /**
     * @var ProgressBar\LogLevelProcessor
     */
    protected $progress;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param ProgressBar\LogLevelProcessor $progress
     * @param Resource\Source $source
     * @param Resource\Destination $destination
     * @param Helper $helper
     * @param Logger $logger
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progress,
        Resource\Source $source,
        Resource\Destination $destination,
        Helper $helper,
        Logger $logger
    ) {
        $this->progress = $progress;
        $this->source = $source;
        $this->destination = $destination;
        $this->helper = $helper;
        parent::__construct($logger);
    }

    /**
     * {@inheritdoc}
     */
    public function perform()
    {
        $this->progress->start(count($this->helper->getSourceDocumentFields()));

        $sourceTotal        = 0;
        $destinationTotal   = $this->destination->getRecordsCount($this->helper->getDestinationName());

        foreach (array_keys($this->helper->getSourceDocumentFields()) as $sourceName) {
            $sourceTotal += $this->source->getRecordsCount($sourceName);
            $this->progress->advance();
        }

        if ($sourceTotal != $destinationTotal) {
            $this->errors[] = 'Mismatch of amount of entities in documents';
        }

        $this->progress->finish();
        return $this->checkForErrors(Logger::ERROR);
    }
}
