<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Log;

class HelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Migration\Step\Log\Helper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    public function setUp()
    {
        $this->helper = new Helper();
    }

    public function testGetDestDocumentsToClear()
    {
        $clearDocuments = [
            'log_customer',
            'log_quote',
            'log_summary',
            'log_summary_type' ,
            'log_url',
            'log_url_info',
            'log_visitor',
            'log_visitor_info',
            'log_visitor_online'
        ];
        $this->assertEquals($clearDocuments, $this->helper->getDestDocumentsToClear());
    }

    public function testGetDocumentList()
    {
        $documentList = ['log_visitor' => 'customer_visitor'];
        $this->assertEquals($documentList, $this->helper->getDocumentList());
    }
}
