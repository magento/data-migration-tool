<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\App;

use Migration\App\Step\StageInterface;
use Migration\MapReader\MapReaderChangelog;
use Migration\Resource\Source;
use Migration\ProgressBar;

class SetupChangeLog implements StageInterface
{
    /**
     * @var Source
     */
    protected $source;

    /**
     * @var MapReaderChangelog
     */
    protected $mapReader;

    /**
     * @var ProgressBar
     */
    protected $progress;

    /**
     * @param Source $source
     * @param MapReaderChangelog $mapReader
     * @param ProgressBar $progress
     */
    public function __construct(Source $source, MapReaderChangelog $mapReader, ProgressBar $progress)
    {
        $this->source = $source;
        $this->mapReader = $mapReader;
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
