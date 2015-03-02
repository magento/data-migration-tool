<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\EavAttributeGroup;

use Migration\Resource\Record;

/**
 * Class SetGroupCodeTest
 */
class SetGroupCodeTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        /** @var \Migration\Resource\Record|\PHPUnit_Framework_MockObject_MockObject $recordToHandle */
        $recordToHandle = $this->getMockBuilder('Migration\Resource\Record')
            ->setMethods(['getValue', 'setValue', 'getFields'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Record $oppositeRecord|\PHPUnit_Framework_MockObject_MockObject */
        $oppositeRecord = $this->getMockBuilder('Migration\Resource\Record')->disableOriginalConstructor()->getMock();

        $fieldName = 'fieldname';
        $recordToHandle->expects($this->once())->method('getFields')->will($this->returnValue([$fieldName]));
        $recordToHandle->expects($this->once())->method('getValue')->with('attribute_group_name')
            ->will($this->returnValue('Some Group Name'));
        $recordToHandle->expects($this->once())->method('setValue')->with($fieldName, 'some-group-name');

        $handler = new SetGroupCode();
        $handler->setField($fieldName);
        $handler->handle($recordToHandle, $oppositeRecord);
    }
}
