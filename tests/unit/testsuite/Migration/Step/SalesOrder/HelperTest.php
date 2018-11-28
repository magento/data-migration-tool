<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\SalesOrder;

use Migration\Reader;
use Migration\ResourceModel\Source;

/**
 * Class Helper
 */
class HelperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Source|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $source;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->source = $this->getMockBuilder(\Migration\ResourceModel\Source::class)
            ->setMethods(['getAdapter', 'addDocumentPrefix'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->helper = new Helper($this->source);
    }

    /**
     * @return void
     */
    public function testGetSourceAttributes()
    {
        $entity = [
            0 => [
                'entity_id' => 1,
                'value' => 'entity_value'
            ]
        ];
        $mySqlAdapter = $this->createPartialMock(
            \Migration\ResourceModel\Adapter\Mysql::class,
            ['getSelect', 'loadDataFromSelect']
        );
        $dbSelect = $this->createPartialMock(
            \Magento\Framework\DB\Select::class,
            ['from', 'where']
        );
        $mySqlAdapter->expects($this->any())->method('getSelect')->willReturn($dbSelect);
        $this->source->expects($this->any())->method('getAdapter')->willReturn($mySqlAdapter);
        $this->source->expects($this->any())->method('addDocumentPrefix')->willReturnArgument(0);
        $dbSelect->expects($this->any())->method('from')->willReturnSelf();
        $dbSelect->expects($this->any())->method('where')->willReturnSelf();
        $mySqlAdapter->expects($this->once())->method('loadDataFromSelect')->willReturn($entity);
        $this->assertEquals($entity, $this->helper->getSourceAttributes('eav_attribute'));
    }

    /**
     * @return void
     */
    public function testGetEavAttributes()
    {
        $eavAttributes = ['reward_points_balance_refunded', 'reward_salesrule_points'];
        $this->assertEquals($eavAttributes, $this->helper->getEavAttributes());
    }

    /**
     * @return void
     */
    public function testGetDocumentList()
    {
        $documentList = ['sales_flat_order' => 'sales_order'];
        $this->assertEquals($documentList, $this->helper->getDocumentList());
    }

    /**
     * @return void
     */
    public function testGetDestEavDocument()
    {
        $destEavDocument = 'eav_entity_int';
        $this->assertEquals($destEavDocument, $this->helper->getDestEavDocument());
    }
}
