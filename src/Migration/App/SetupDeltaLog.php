<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\App;

use Migration\MapReader\MapReaderDeltalog;
use Migration\Resource\Source;
use Migration\MapReader\MapReaderMain;
use Migration\ProgressBar;

class SetupDeltaLog
{
    /**
     * @var Source
     */
    protected $source;

    /**
     * @var MapReaderMain
     */
    protected $mapReader;

    /**
     * @var ProgressBar
     */
    protected $progress;

    /**
     * @param Source $source
     * @param MapReaderDeltalog $mapReader
     * @param ProgressBar $progress
     */
    public function __construct(Source $source, MapReaderDeltalog $mapReader, ProgressBar $progress)
    {
        $this->source = $source;
        $this->mapReader = $mapReader;
        $this->progress = $progress;
    }

    /**
     * @return bool
     */
    public function setupDeltaLog()
    {
        $deltaDocuments = $this->mapReader->getDeltaDocuments($this->source->getDocumentList());
        $this->progress->start(count($deltaDocuments));
        foreach ($deltaDocuments as $documentName => $idKey) {
            $this->progress->advance();
            $this->source->createDelta($documentName, $idKey);
        }
        $this->progress->finish();
        return true;
    }
}
