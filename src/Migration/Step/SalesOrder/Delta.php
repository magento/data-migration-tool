<?php
/**
 * Created by PhpStorm.
 * User: lpoluyanov
 * Date: 18.03.2015
 * Time: 16:16
 */

namespace Migration\Step\SalesOrder;

use Migration\Resource\Source;
use Migration\MapReader\MapReaderSalesOrder;
use Migration\ProgressBar;

class Delta
{
    /**
     * @var Source
     */
    protected $source;

    /**
     * @var MapReaderSalesOrcer
     */
    protected $mapReader;

    /**
     * @var ProgressBar
     */
    protected $progress;

    /**
     * @param Source $source
     * @param MapReaderSalesOrder $mapReader
     * @param ProgressBar $progress
     */
    public function __construct(Source $source, MapReaderSalesOrder $mapReader, ProgressBar $progress)
    {
        $this->source = $source;
        $this->mapReader = $mapReader;
        $this->progress = $progress;
    }

    /**
     * @return void
     */
    public function setUpChangeLog()
    {
        $deltaDocuments = $this->mapReader->getDeltaDocuments($this->source->getDocumentList());
        $this->progress->start(count($deltaDocuments));
        foreach ($deltaDocuments as $documentName => $idKey) {
            $this->progress->advance();
            $this->source->createDelta($documentName, $this->source->getChangeLogName($documentName), $idKey);
        }
        $this->progress->finish();
        return true;
    }
}
