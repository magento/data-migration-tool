<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\SalesOrderStatusState;

use Migration\Resource\Record;

/**
 * Class SetVisibleOnFrontTest
 */
class SetVisibleOnFrontTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Record
     */
    protected $recordToHandle;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Record
     */
    protected $oppositeRecord;

    public function setUp()
    {
        $this->recordToHandle = $this->getMockBuilder('Migration\Resource\Record')
            ->setMethods(['getValue', 'setValue', 'getFields'])
            ->disableOriginalConstructor()->getMock();
        $this->oppositeRecord = $this->getMockBuilder('Migration\Resource\Record')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testHandleInitiallyInvisible()
    {
        $fieldName = 'visible_on_front';
        $this->recordToHandle->expects($this->once())->method('getFields')->will($this->returnValue([$fieldName]));
        $this->recordToHandle->expects($this->any())->method('getValue')->will($this->returnCallback(function ($value) {
            switch ($value) {
                case 'status':
                    return 'pending_ogone';
                    break;
                case 'state':
                    return 'pending_payment';
                    break;
            }
            return '';
        }));
        $this->recordToHandle->expects($this->once())->method('setValue')->with($fieldName, 0);

        $handler = new SetVisibleOnFront();
        $handler->setField($fieldName);
        $handler->handle($this->recordToHandle, $this->oppositeRecord);
    }

    public function testHandleNotVisibleStates()
    {
        $fieldName = 'visible_on_front';
        $this->recordToHandle->expects($this->once())->method('getFields')->will($this->returnValue([$fieldName]));
        $this->recordToHandle->expects($this->any())->method('getValue')->will($this->returnCallback(function ($value) {
            switch ($value) {
                case 'status':
                    return 'some_my_status';
                    break;
                case 'state':
                    return 'pending_payment';
                    break;
            }
            return '';
        }));
        $this->recordToHandle->expects($this->once())->method('setValue')->with($fieldName, 0);

        $handler = new SetVisibleOnFront();
        $handler->setField($fieldName);
        $handler->handle($this->recordToHandle, $this->oppositeRecord);
    }

    /**
     * @dataProvider stateProvider
     * @param string $state
     */
    public function testHandleVisibleState($state)
    {
        $fieldName = 'visible_on_front';
        $this->recordToHandle->expects($this->once())->method('getFields')->will($this->returnValue([$fieldName]));
        $this->recordToHandle->expects($this->any())->method('getValue')->will($this->returnCallback(
            function ($value) use ($state) {
                switch ($value) {
                    case 'status':
                        return 'some_my_status';
                        break;
                    case 'state':
                        return $state;
                        break;
                }
                return '';
            }
        ));
        $this->recordToHandle->expects($this->once())->method('setValue')->with($fieldName, 1);

        $handler = new SetVisibleOnFront();
        $handler->setField($fieldName);
        $handler->handle($this->recordToHandle, $this->oppositeRecord);
    }

    public function stateProvider()
    {
        return [['new'], ['processing'], ['complete'], ['closed'], ['canceled'], ['holded'], ['payment_review']];
    }
}
