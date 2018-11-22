<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\SerializeToJson;

class ItemOptionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Migration\ResourceModel\Record|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    public function setUp()
    {
        $this->model = $this->getMockBuilder(\Migration\ResourceModel\Record::class)
            ->setMethods(['setValue', 'getValue', 'getFields', 'getData'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param string $code
     * @param string $serialized
     * @param string $expectedJson
     *
     * @dataProvider handleDataProvider
     */
    public function testHandle($code, $serialized, $expectedJson)
    {
        $fieldName = 'fieldname';
        $this->model->expects($this->any())->method('getFields')->willReturn([$fieldName]);
        $this->model->expects($this->any())->method('getData')->willReturn(['code' => $code]);
        $this->model->expects($this->any())->method('getValue')->with($fieldName)->willReturn($serialized);
        $this->model->expects($this->any())->method('setValue')->with($fieldName, $expectedJson);
        $record2 = $this->getMockBuilder(
            \Migration\ResourceModel\Record::class
        )->disableOriginalConstructor()->getMock();
        $handler = new ItemOption();
        $handler->setField($fieldName);
        $this->assertNull($handler->handle($this->model, $record2));
    }

    /**
     * @expectedException \Migration\Exception
     */
    public function testException()
    {
        $fieldName = 'fieldname';
        $this->model->expects($this->any())->method('getFields')->willReturn([$fieldName]);
        $this->model->expects($this->any())->method('getData')->willReturn(['some_value' => null]);
        $documentIdField = $this->getMockBuilder(\Migration\Model\DocumentIdField::class)
            ->setMethods(['getFiled'])
            ->disableOriginalConstructor()
            ->getMock();
        $logger = $this->getMockBuilder(\Migration\Logger\Logger::class)
            ->setMethods(['warning'])
            ->disableOriginalConstructor()
            ->getMock();
        $handler = new ConvertWithConditions('code', null, $logger, $documentIdField);
        $handler->setField($fieldName);
        $handler->handle($this->model, $this->model);
    }

    /**
     * @return array
     */
    public function handleDataProvider()
    {
        return [
            'dataset_1' => [
                'option_1',
                'serialized' => 'a:1:{s:4:"some";s:5:"value";}',
                'expectedJson' => '{"some":"value"}',
            ],
            'dataset_2' => [
                'some_option',
                'serialized' => 1,
                'expectedJson' => 1,
            ],
        ];
    }
}
