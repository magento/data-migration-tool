<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Handler;

class ConvertTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function dataProviderMaps()
    {
        return [
            [
                'map' => '[one_column:1column;two_columns_left:2columns-left]',
                'initialValue' => 'one_column',
                'processedValue' => '1column'
            ], [
                'map' => '[one_column:1column;two_columns_left:2columns-left]',
                'initialValue' => 'dummy_value',
                'processedValue' => 'dummy_value'
            ]
        ];
    }

    /**
     * @param $map
     * @param $initialValue
     * @param $processedValue
     * @dataProvider dataProviderMaps
     */
    public function testHandle($map, $initialValue, $processedValue)
    {
        $fieldName = 'fieldname';
        /** @var \Migration\Resource\Record|\PHPUnit_Framework_MockObject_MockObject $record */
        $record = $this->getMock('Migration\Resource\Record', ['setValue', 'getValue'], [], '', false);
        $record->expects($this->once())->method('getValue')->will($this->returnValue($initialValue));
        $record->expects($this->once())->method('setValue')->with($fieldName, $processedValue);
        $handler = new Convert($map);
        $handler->handle($record, $fieldName);
    }

    public function testInvalidMap()
    {
        $this->setExpectedException('Exception');
        $handler = new Convert('[dummy]');
        $record = $this->getMock('Migration\Resource\Record', [], [], '', false);
        $handler->handle($record, 'dummy');
    }
}
