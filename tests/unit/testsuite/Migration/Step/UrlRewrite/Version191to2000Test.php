<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Step\UrlRewrite;

/**
 * Class Version19Test
 * Test for \Migration\Step\UrlRewrite\Version19
 */
class Version191to2000Test extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Migration\App\ProgressBar\LogLevelProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $progress;

    /**
     * @var \Migration\Logger\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var \Migration\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var \Migration\ResourceModel\Source|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $source;

    /**
     * @var \Migration\ResourceModel\Destination|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $destination;

    /**
     * @var \Migration\ResourceModel\Record\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $recordCollection;

    /**
     * @var \Migration\ResourceModel\RecordFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $recordFactory;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->logger = $this->getMock('Migration\Logger\Logger', ['error'], [], '', false);
        $this->progress = $this->getMock(
            '\Migration\App\ProgressBar\LogLevelProcessor',
            ['start', 'finish', 'advance'],
            [],
            '',
            false
        );
        $this->logger = $this->getMock('\Migration\Logger\Logger', ['debug', 'error'], [], '', false);
        $this->config = $this->getMock('\Migration\Config', [], [], '', false);
        $this->config->expects($this->any())->method('getSource')->willReturn([
            'type' => 'database',
            'version' => '1.9'
        ]);
        $this->source = $this->getMock('\Migration\ResourceModel\Source', [], [], '', false);

        $select = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->setMethods(['from', 'joinLeft'])
            ->disableOriginalConstructor()
            ->getMock();

        $select->expects($this->any())->method('from')->willReturnSelf();
        $select->expects($this->any())->method('joinLeft')->willReturnSelf();

        $sourceAdapter = $this->getMockBuilder('Migration\ResourceModel\Adapter\Mysql')->disableOriginalConstructor()
            ->setMethods(['getSelect', 'loadDataFromSelect'])
            ->getMock();

        $sourceAdapter->expects($this->any())->method('getSelect')->willReturn($select);

        $this->source->expects($this->any())->method('getAdapter')->willReturn($sourceAdapter);

        $this->destination = $this->getMock('\Migration\ResourceModel\Destination', [], [], '', false);
        $this->recordCollection = $this->getMock(
            '\Migration\ResourceModel\Record\Collection',
            ['addRecord'],
            [],
            '',
            false
        );
        $this->recordFactory = $this->getMock('\Migration\ResourceModel\RecordFactory', ['create'], [], '', false);
    }

    /**
     * @return void
     */
    public function testRollback()
    {
        $version = new \Migration\Step\UrlRewrite\Version191to2000(
            $this->config,
            $this->source,
            $this->destination,
            $this->progress,
            $this->recordFactory,
            $this->logger,
            'data'
        );
        $this->assertTrue($version->rollback());
    }

    /**
     * @throws \Migration\Exception
     * @return void
     */
    public function testIntegrity()
    {
        $this->progress->expects($this->at(0))
            ->method('start')
            ->with($this->equalTo(1));
        $sourceStructure = $this->getMockBuilder('\Migration\ResourceModel\Structure')
            ->disableOriginalConstructor()
            ->getMock();
        $sourceStructure->expects($this->once())
            ->method('getFields')
            ->willReturn([
                'url_rewrite_id' => 'something',
                'store_id' => 'something',
                'id_path' => 'something',
                'request_path' => 'something',
                'target_path' => 'something',
                'is_system' => 'something',
                'options' => 'something',
                'description' => 'something',
                'category_id' => 'something',
                'product_id' => 'something',
            ]);
        $this->source->expects($this->at(0))
            ->method('getStructure')
            ->with($this->equalTo(\Migration\Step\UrlRewrite\Version191to2000::SOURCE))
            ->willReturn($sourceStructure);
        $destinationStructure = $this->getMockBuilder('\Migration\ResourceModel\Structure')
            ->disableOriginalConstructor()
            ->getMock();
        $destinationStructure->expects($this->once())
            ->method('getFields')
            ->willReturn([
                'url_rewrite_id' => 'something',
                'entity_type' => 'something',
                'entity_id' => 'something',
                'request_path' => 'something',
                'target_path' => 'something',
                'redirect_type' => 'something',
                'store_id' => 'something',
                'description' => 'something',
                'is_autogenerated' => 'something',
                'metadata' => 'something',
            ]);
        $this->destination->expects($this->at(0))
            ->method('getStructure')
            ->with($this->equalTo(\Migration\Step\UrlRewrite\Version191to2000::DESTINATION))
            ->willReturn($destinationStructure);
        $this->progress->expects($this->once())
            ->method('advance')
            ->willReturnSelf();
        $this->progress->expects($this->once())
            ->method('finish')
            ->willReturnSelf();
        $version = new \Migration\Step\UrlRewrite\Version191to2000(
            $this->config,
            $this->source,
            $this->destination,
            $this->progress,
            $this->recordFactory,
            $this->logger,
            'integrity'
        );

        $this->assertTrue($version->perform());
    }

    /**
     * @throws \Migration\Exception
     * @return void
     */
    public function testData()
    {
        $countCmsPageRewrites = 1;
        $recordsAmount = 123;
        $progressRecordsAmount = $recordsAmount + $countCmsPageRewrites;

        $this->source->expects($this->once())
            ->method('getRecordsCount')
            ->willReturn($recordsAmount);
        $this->progress->expects($this->at(0))
            ->method('start')
            ->with($this->equalTo($progressRecordsAmount));

        $sourceDocument = $this->getMockBuilder('\Migration\ResourceModel\Document')
            ->disableOriginalConstructor()
            ->getMock();
        $this->source->expects($this->once())
            ->method('getDocument')
            ->with($this->equalTo(\Migration\Step\UrlRewrite\Version191to2000::SOURCE))
            ->willReturn($sourceDocument);
        $destinationDocument = $this->getMockBuilder('\Migration\ResourceModel\Document')
            ->disableOriginalConstructor()
            ->getMock();
        $this->destination->expects($this->at(0))
            ->method('getDocument')
            ->with($this->equalTo(\Migration\Step\UrlRewrite\Version191to2000::DESTINATION))
            ->willReturn($destinationDocument);
        $destinationProductCategory = $this->getMockBuilder('\Migration\ResourceModel\Document')
            ->setMethods(['setValue', 'getRecords'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->destination->expects($this->at(1))
            ->method('getDocument')
            ->with($this->equalTo(\Migration\Step\UrlRewrite\Version191to2000::DESTINATION_PRODUCT_CATEGORY))
            ->willReturn($destinationProductCategory);

        $this->destination->expects($this->exactly(2))
            ->method('clearDocument')
            ->withConsecutive(
                [\Migration\Step\UrlRewrite\Version191to2000::DESTINATION],
                [\Migration\Step\UrlRewrite\Version191to2000::DESTINATION_PRODUCT_CATEGORY]
            );

        $this->source->expects($this->at(2))
            ->method('getRecords')
            ->with($this->equalTo(\Migration\Step\UrlRewrite\Version191to2000::SOURCE), $this->equalTo(0))
            ->willReturn(['RecordData1']);

        $sourceRecord = $this->getMockBuilder('\Migration\ResourceModel\Record')
            ->disableOriginalConstructor()
            ->getMock();

        $this->recordFactory->expects($this->at(0))
            ->method('create')
            ->with($this->equalTo(['document' => $sourceDocument, 'data' => 'RecordData1']))
            ->willReturn($sourceRecord);

        $destinationRecord = $this->getMockBuilder('\Migration\ResourceModel\Record')
            ->disableOriginalConstructor()
            ->getMock();

        $this->recordFactory->expects($this->at(1))
            ->method('create')
            ->with($this->equalTo(['document' => $destinationDocument]))
            ->willReturn($destinationRecord);

        $destinationCategoryRecord = $this->getMockBuilder('\Migration\ResourceModel\Record')
            ->disableOriginalConstructor()
            ->getMock();

        $this->recordFactory->expects($this->at(2))
            ->method('create')
            ->with($this->equalTo(['document' => $destinationProductCategory]))
            ->willReturn($destinationCategoryRecord);

        $this->mockSourceRecordGetters($sourceRecord);

        $this->mockDestinationRecordSetters($destinationRecord);

        $this->mockDestinationCategorySetters($destinationCategoryRecord);

        $destinationProductCategory->expects($this->once())
            ->method('getRecords')
            ->willReturn($this->recordCollection);
        $destinationDocument->expects($this->once())
            ->method('getRecords')
            ->willReturn($this->recordCollection);

        $version = new \Migration\Step\UrlRewrite\Version191to2000(
            $this->config,
            $this->source,
            $this->destination,
            $this->progress,
            $this->recordFactory,
            $this->logger,
            'data'
        );
        $this->assertTrue($version->perform());
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $sourceRecord
     * @return void
     */
    private function mockSourceRecordGetters($sourceRecord)
    {
        $sourceRecord->expects($this->any())
            ->method('getValue')
            ->willReturnMap([
                ['url_rewrite_id', 'url_rewrite_id_value'],
                ['store_id', 'store_id_value'],
                ['description', 'description_value'],
                ['request_path', 'request_path_value'],
                ['target_path', 'target_path_value'],
                ['is_autogenerated', 'is_autogenerated_value'],
                ['product_id', 'product_id_value'],
                ['is_system', 'is_system_value'],
                ['category_id', 'category_id_value']
            ]);
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $destinationRecord
     * @return void
     */
    private function mockDestinationRecordSetters($destinationRecord)
    {
        $destinationRecord->expects($this->at(0))
            ->method('setValue')
            ->with('url_rewrite_id', 'url_rewrite_id_value')
            ->willReturnSelf();
        $destinationRecord->expects($this->at(1))
            ->method('setValue')
            ->with('store_id', 'store_id_value')
            ->willReturnSelf();
        $destinationRecord->expects($this->at(2))
            ->method('setValue')
            ->with('description', 'description_value')
            ->willReturnSelf();
        $destinationRecord->expects($this->at(3))
            ->method('setValue')
            ->with('request_path', 'request_path_value')
            ->willReturnSelf();
        $destinationRecord->expects($this->at(4))
            ->method('setValue')
            ->with('target_path', 'target_path_value')
            ->willReturnSelf();
        $destinationRecord->expects($this->at(5))
            ->method('setValue')
            ->with('is_autogenerated', 'is_system_value')
            ->willReturnSelf();
        $destinationRecord->expects($this->at(6))
            ->method('setValue')
            ->with('entity_type', 'product')
            ->willReturnSelf();
        $destinationRecord->expects($this->at(7))
            ->method('setValue')
            ->with('metadata', 'a:1:{s:11:"category_id";s:17:"category_id_value";}')->willReturnSelf();
        $destinationRecord->expects($this->at(8))
            ->method('setValue')
            ->with('entity_id', 'product_id_value')
            ->willReturnSelf();
        $destinationRecord->expects($this->at(9))
            ->method('setValue')
            ->with('redirect_type', '0')
            ->willReturnSelf();
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $destinationCategoryRecord
     * @return void
     */
    private function mockDestinationCategorySetters($destinationCategoryRecord)
    {
        $destinationCategoryRecord->expects($this->at(0))
            ->method('setValue')
            ->with('url_rewrite_id', 'url_rewrite_id_value')
            ->willReturnSelf();
        $destinationCategoryRecord->expects($this->at(1))
            ->method('setValue')
            ->with('category_id', 'category_id_value')
            ->willReturnSelf();
        $destinationCategoryRecord->expects($this->at(2))
            ->method('setValue')
            ->with('product_id', 'product_id_value')
            ->willReturnSelf();
    }
}
