<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\SerializeToJson;

class ConvertWithConditionsTest extends \PHPUnit\Framework\TestCase
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

        $this->model->expects($this->any())->method('getFields')->willReturn([$fieldName]);
        $this->model->expects($this->any())->method('getData')->willReturn(['code' => 'info_buyRequest']);
        $this->model->expects($this->any())->method('getValue')->with($fieldName)->willReturn($dataToConvert);
        $this->model->expects($this->any())->method('setValue')->with($fieldName, $expectedResult);
        $handler = new ConvertWithConditions(
            $conditionalField,
            $conditionalFieldValuesPattern,
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
        $handler = new ConvertWithConditions('code', null);
        $handler->setField($fieldName);
        $handler->handle($this->model, $this->model);
    }

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
                false,
                null,
                null
            ],
            [
                'code',
                '/(parameters)|(info_buyRequest)|(bundle_option_ids)|(bundle_selection_attributes)/',
                true,
                'brokenString',
                json_encode([])
            ],
        ];
    }
}
