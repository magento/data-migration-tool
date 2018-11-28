<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

class ClassMapTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testHandle()
    {
        $classOldFashion = 'catalog/product_widget_link';
        $classNewStyle = 'Magento\\Catalog\\Block\\Product\\Widget\\Link';
        $fieldName = 'fieldname';
        /** @var \Migration\ResourceModel\Record|\PHPUnit_Framework_MockObject_MockObject $record */
        $record = $this->createPartialMock(
            \Migration\ResourceModel\Record::class,
            ['getValue', 'setValue', 'getFields']
        );
        $record->expects($this->once())->method('getValue')->with($fieldName)->willReturn($classOldFashion);
        $record->expects($this->once())->method('setValue')->with($fieldName, $classNewStyle);
        $record->expects($this->once())->method('getFields')->will($this->returnValue([$fieldName]));
        $record2 = $this->getMockBuilder(\Migration\ResourceModel\Record::class)
            ->disableOriginalConstructor()
            ->getMock();
        $classMap = $this->createPartialMock(
            \Migration\Reader\ClassMap::class,
            ['convertClassName']
        );
        $classMap->expects($this->once())
            ->method('convertClassName')
            ->with($classOldFashion)
            ->willReturn($classNewStyle);
        $handler = new \Migration\Handler\ClassMap($classMap);
        $handler->setField($fieldName);
        $handler->handle($record, $record2);
    }
}
