<?php
/**
 * Created by PhpStorm.
 * User: lpoluyanov
 * Date: 18.03.2015
 * Time: 16:16
 */

namespace Migration\Step\SalesOrder;

use Migration\Step\DeltaInterface;
use Migration\Resource\Source;
use Migration\MapReader\MapReaderSalesOrder;

class Delta implements DeltaInterface
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
     * @param Source $source
     * @param MapReaderSalesOrder $mapReader
     */
    public function __construct(Source $source, MapReaderSalesOrder $mapReader)
    {
        $this->source = $source;
        $this->mapReader = $mapReader;
    }

    /**
     * @return void
     */
    public function setUpDelta()
    {
        $deltaDocuments = $this->mapReader->getDeltaDocuments($this->source->getDocumentList());
        foreach ($deltaDocuments as $document) {
            $this->source->createDelta($document);
        }
    }
} 