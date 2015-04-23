<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\App;

use Migration\Reader\Map;
use Migration\App\Step\StageInterface;
use Migration\Resource\Source;

class SetupDeltaLog implements StageInterface
{
    /**
     * @var Source
     */
    protected $source;

    /**
     * @var Map
     */
    protected $mapReader;

    /**
     * @var ProgressBar
     */
    protected $progress;

    /**
     * @param Source $source
     * @param \Migration\Reader\MapFactory $mapFactory
     * @param ProgressBar $progress
     */
    public function __construct(Source $source, \Migration\Reader\MapFactory $mapFactory, ProgressBar $progress)
    {
        $this->source = $source;
        $this->mapReader = $mapFactory->create('deltalog_map_file');
        $this->progress = $progress;
    }

    /**
     * @return bool
     */
    public function perform()
    {
        $deltaDocuments = $this->mapReader->getDeltaDocuments();
        $this->progress->start(count($deltaDocuments));
        foreach ($deltaDocuments as $documentName => $idKey) {
            $this->progress->advance();
            $this->source->createDelta($documentName, $idKey);
        }
        $this->progress->finish();
        return true;
    }
}
