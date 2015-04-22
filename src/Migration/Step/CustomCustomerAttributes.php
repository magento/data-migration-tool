<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

use Migration\App\ProgressBar;
use Migration\Resource\Destination;
use Migration\Resource\Document;
use Migration\Resource\Record;
use Migration\Resource\Source;
use Migration\Config;

/**
 * Class CustomerAttributesSalesFlat
 */
abstract class CustomCustomerAttributes extends DatabaseStage
{
    /**
     * @var Source
     */
    protected $source;

    /**
     * @var Destination
     */
    protected $destination;

    /**
     * @var ProgressBar
     */
    protected $progress;

    /**
     * @param Config $config
     * @param Source $source
     * @param Destination $destination
     * @param ProgressBar $progress
     * @throws \Migration\Exception
     */
    public function __construct(
        Config $config,
        Source $source,
        Destination $destination,
        ProgressBar $progress
    ) {
        parent::__construct($config);
        $this->source = $source;
        $this->destination = $destination;
        $this->progress = $progress;
    }

    /**
     * @return array
     */
    public function getDocumentList()
    {
        return [
            'enterprise_customer_sales_flat_order' =>
                'magento_customercustomattributes_sales_flat_order',

            'enterprise_customer_sales_flat_order_address' =>
                'magento_customercustomattributes_sales_flat_order_address',

            'enterprise_customer_sales_flat_quote' =>
                'magento_customercustomattributes_sales_flat_quote',

            'enterprise_customer_sales_flat_quote_address' =>
                'magento_customercustomattributes_sales_flat_quote_address'
        ];
    }
}
