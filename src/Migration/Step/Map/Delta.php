<?php
/**
 * Created by PhpStorm.
 * User: lpoluyanov
 * Date: 18.03.2015
 * Time: 16:16
 */

namespace Migration\Step\Map;

use Migration\Resource\Source;
use Migration\MapReader\MapReaderMain;
use Migration\ProgressBar;

class Delta
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
     * @param MapReaderMain $mapReader
     * @param ProgressBar $progress
     */
    public function __construct(Source $source, MapReaderMain $mapReader, ProgressBar $progress)
    {
        $this->source = $source;
        $this->mapReader = $mapReader;
        $this->progress = $progress;
    }

    /**
     * @return void
     */
    public function setUpDelta()
    {
        $deltaDocuments = $this->mapReader->getDeltaDocuments($this->source->getDocumentList());
        $this->progress->start(count($deltaDocuments));
        foreach ($deltaDocuments as $documentName => $idKey) {
            $this->progress->advance();
            $this->source->createDelta($documentName, $this->source->getChangeLogName($documentName), $idKey);
        }
        $this->progress->finish();
    }
} 