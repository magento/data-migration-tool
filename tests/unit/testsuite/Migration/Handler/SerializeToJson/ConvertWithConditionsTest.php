<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\SerializeToJson;

class ConvertWithConditionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Migration\ResourceModel\Record|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    /**
     * @var \Migration\Logger\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var \Migration\Model\DocumentIdField|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $documentIdField;

    public function setUp()
    {
        $this->model = $this->getMockBuilder(\Migration\ResourceModel\Record::class)
            ->setMethods(['setValue', 'getValue', 'getFields', 'getData', 'getDocument'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMockBuilder(\Migration\Logger\Logger::class)
            ->setMethods(['warning'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->documentIdField = $this->getMockBuilder(\Migration\Model\DocumentIdField::class)
            ->setMethods(['getFiled'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param string $dataToConvert
     * @param string $expectedJson
     *
     * @dataProvider convertDataProvider
     */
    public function testHandle(
        $conditionalField,
        $conditionalFieldValuesPattern,
        $ignoreBrokenData,
        $dataToConvert,
        $expectedResult
    ) {
        $fieldName = 'fieldname';
        $document = $this->getMockBuilder(\Migration\ResourceModel\Document::class)
            ->setMethods(['getName'])
            ->disableOriginalConstructor()
            ->getMock();
        $document->expects($this->any())->method('getName')->willReturn('document1');
        $this->documentIdField->expects($this->any())->method('getFiled')->with($document)->willReturn('fieldid');

        $this->model->expects($this->any())->method('getDocument')->willReturn($document);
        $this->model->expects($this->any())->method('getFields')->willReturn([$fieldName]);
        $this->model->expects($this->any())->method('getData')->willReturn(['code' => 'info_buyRequest']);
        $this->model->expects($this->any())->method('getValue')->willReturnMap(
            [
                [$fieldName, $dataToConvert],
                ['fieldid', 5]
            ]
        );
        $this->model->expects($this->any())->method('setValue')->with($fieldName, $expectedResult);
        $handler = new ConvertWithConditions(
            $conditionalField,
            $conditionalFieldValuesPattern,
            $this->logger,
            $this->documentIdField,
            $ignoreBrokenData
        );
        $handler->setField($fieldName);
        $this->assertNull($handler->handle($this->model, $this->model));
    }

    /**
     * @expectedException \Migration\Exception
     */
    public function testException()
    {
        $fieldName = 'fieldname';
        $this->model->expects($this->any())->method('getFields')->willReturn([$fieldName]);
        $this->model->expects($this->any())->method('getData')->willReturn(['some_value' => null]);
        $handler = new ConvertWithConditions('code', null, $this->logger, $this->documentIdField);
        $handler->setField($fieldName);
        $handler->handle($this->model, $this->model);
    }

    /**
     * @return array
     */
    public function convertDataProvider()
    {
        $data = ['product' => '2', 'form_key' => '2SYziDL1rBficzaP'];
        return [
            [
                'code',
                '/(parameters)|(info_buyRequest)|(bundle_option_ids)|(bundle_selection_attributes)/',
                false,
                serialize($data),
                json_encode($data)
            ],
            [
                null,
                null,
                true,
                null,
                null
            ],
            [
                'code',
                '/(parameters)|(info_buyRequest)|(bundle_option_ids)|(bundle_selection_attributes)/',
                true,
                'brokenString',
                null
            ],
        ];
    }
}
