<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

class ClassMapTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $classOldFashion = 'catalog/product_widget_new';
        $classNewStyle = 'Magento\\Catalog\\Block\\Product\\Widget\\NewWidget';
        $fieldName = 'fieldname';
        /** @var \Migration\Resource\Record|\PHPUnit_Framework_MockObject_MockObject $record */
        $record = $this->getMock('Migration\Resource\Record', ['getValue', 'setValue', 'getFields'], [], '', false);
        $record->expects($this->once())->method('getValue')->with($fieldName)->willReturn($classOldFashion);
        $record->expects($this->once())->method('setValue')->with($fieldName, $classNewStyle);
        $record->expects($this->once())->method('getFields')->will($this->returnValue([$fieldName]));
        $record2 = $this->getMockBuilder('Migration\Resource\Record')->disableOriginalConstructor()->getMock();
        $classMap = $this->getMock('Migration\Reader\ClassMap', ['convertClassName'], [], '', false);
        $classMap->expects($this->once())
            ->method('convertClassName')
            ->with($classOldFashion)
            ->willReturn($classNewStyle);
        $handler = new \Migration\Handler\ClassMap($classMap);
        $handler->setField($fieldName);
        $handler->handle($record, $record2);
    }
}
