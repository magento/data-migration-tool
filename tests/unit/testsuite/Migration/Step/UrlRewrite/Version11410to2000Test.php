<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

/**
 * Class UrlRewriteTest
 */
class Version11410to2000Test extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Migration\Step\UrlRewrite\Version11410to2000
     */
    protected $version;

    /**
     * @var \Migration\Step\Progress|\PHPUnit_Framework_MockObject_MockObject
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
     * @var \Migration\Resource\Source|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $source;

    /**
     * @var \Migration\Resource\Destination|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $destination;

    /**
     * @var \Migration\Resource\Record\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $recordCollectionFactory;

    /**
     * @var \Migration\Resource\RecordFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $recordFactory;

    public function setUp()
    {
        $this->progress = $this->getMockBuilder('\Migration\Step\Progress')
            ->setMethods(['getProgress', 'getMaxSteps', 'advance', 'finish', 'setStep', 'reset', 'start'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMock('\Migration\Logger\Logger', ['debug', 'error'], [], '', false);
        $this->config = $this->getMock('\Migration\Config', [], [], '', false);
        $this->config->expects($this->any())->method('getSource')->willReturn([
            'type' => 'database',
            'version' => '1.14.1.0'
        ]);
        $this->source = $this->getMock('\Migration\Resource\Source', [], [], '', false);
        $this->destination = $this->getMock('\Migration\Resource\Destination', [], [], '', false);
        $this->recordCollectionFactory = $this->getMock(
            '\Migration\Resource\Record\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->recordFactory = $this->getMock('\Migration\Resource\RecordFactory', ['create'], [], '', false);

        $this->version = new \Migration\Step\UrlRewrite\Version11410to2000(
            $this->config,
            $this->source,
            $this->destination,
            $this->recordCollectionFactory,
            $this->recordFactory
        );
    }

    public function testRun()
    {
        $select = $this->getMock('\Magento\Framework\DB\Select', [], [], '', false);
        $select->expects($this->once())
            ->method('from')
            ->with(['r' => 'prefix_enterprise_url_rewrite'])
            ->willReturnSelf();

        $select->expects($this->any())
            ->method('joinLeft')
            ->withConsecutive(
                [
                    ['c' => 'prefix_catalog_category_entity_url_key'],
                    'r.entity_type = 2 and r.value_id = c.value_id',
                    ['category_id' => 'entity_id']
                ],
                [
                    ['p' => 'prefix_catalog_product_entity_url_key'],
                    'r.entity_type = 3 and r.value_id = p.value_id',
                    ['product_id' => 'entity_id']
                ],
                [
                    ['t' => 'prefix_enterprise_url_rewrite_redirect'],
                    'r.entity_type = 1 and r.value_id = t.redirect_id',
                    ['r_category_id' => 'category_id', 'r_product_id' => 'product_id', 'options']
                ]
            )
            ->willReturnSelf();
        $select->expects($this->any())->method('limit')->with(3, 0)->willReturnSelf();

        $adapter = $this->getMock('\Migration\Resource\Adapter\Mysql', [], [], '', false);
        $adapter->expects($this->any())->method('getSelect')->willReturn($select);
        $adapter->expects($this->any())->method('loadDataFromSelect')->with($select)->willReturn([
            ''
        ]);

        $this->source->expects($this->any())->method('getAdapter')->willReturn($adapter);
        $this->source->expects($this->any())->method('addDocumentPrefix')->willReturnMap([
            ['enterprise_url_rewrite', 'prefix_enterprise_url_rewrite'],
            ['catalog_category_entity_url_key', 'prefix_catalog_category_entity_url_key'],
            ['catalog_product_entity_url_key', 'prefix_catalog_product_entity_url_key'],
            ['enterprise_url_rewrite_redirect', 'prefix_enterprise_url_rewrite_redirect'],
        ]);
        $this->source->expects($this->any())->method('getPageSize')->willReturn(3);
        $this->source->expects($this->any())->method('getRecordsCount')->willReturn(1);

        $sourceDocument = $this->getMock('\Migration\Resource\Document', [], [], '', false);
        $this->source->expects($this->any())->method('getDocument')->with('enterprise_url_rewrite')
            ->willReturn($sourceDocument);

        $urlRewrite = $this->getMock('\Migration\Resource\Document', [], [], '', false);
        $categoryVarchar = $this->getMock('\Migration\Resource\Document', [], [], '', false);
        $productVarchar = $this->getMock('\Migration\Resource\Document', [], [], '', false);

        $this->destination->expects($this->any())
            ->method('getDocument')
            ->willReturnMap([
                ['url_rewrite', $urlRewrite],
                ['catalog_category_entity_varchar', $categoryVarchar],
                ['catalog_product_entity_varchar', $productVarchar],
            ]);

        $recordCollection = $this->getMock('\Migration\Resource\Record\Collection', [], [], '', false);
        $this->recordCollectionFactory->expects($this->any())->method('create')->willReturn($recordCollection);
        $record = $this->getMock('\Migration\Resource\Record', [], [], '', false);
        $this->recordFactory->expects($this->any())->method('create')->willReturn($record);

        $this->version->run();
    }
}
