<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = $this->getMock(
            'Magento\Framework\ObjectManager\ObjectManager',
            ['create'],
            [],
            '',
            false
        );
        $this->manager = new Manager($this->objectManager);
    }

    /**
     * @covers \Migration\Handler\Manager::initHandler
     * @covers \Migration\Handler\Manager::getHandler
     * @return void
     */
    public function testGetHandlerCorrect()
    {
        $field = 'someField';
        $handlerConfig = ['class' => 'Migration\Handler\SetValue', 'params' => ['value' => '12']];
        $handler = $this->getMock('Migration\Handler\SetValue', ['setField'], [], '', false);
        $this->objectManager->expects($this->any())->method('create')->will($this->returnValue($handler));
        $handler->expects($this->once())->method('setField')->with($field);
        $this->manager->initHandler($field, $handlerConfig);
        $this->assertEquals($handler, $this->manager->getHandler($field));
        $this->assertEquals([$field => $handler], $this->manager->getHandlers());
    }

    /**
     * @throws \Migration\Exception
     * @return void
     */
    public function testGetHandlerWithHandlerKey()
    {
        $field = 'someField';
        $handlerKey = 'someKey';
        $handlerConfig = ['class' => 'Migration\Handler\SetValue', 'params' => ['value' => '12']];
        $handler = $this->getMock('Migration\Handler\SetValue', ['setField'], [], '', false);
        $this->objectManager->expects($this->any())->method('create')->will($this->returnValue($handler));
        $handler->expects($this->once())->method('setField')->with($field);
        $this->manager->initHandler($field, $handlerConfig, $handlerKey);
        $this->assertEquals($handler, $this->manager->getHandler($handlerKey));
        $this->assertEquals([$handlerKey => $handler], $this->manager->getHandlers());
    }

    /**
     * @covers \Migration\Handler\Manager::initHandler
     * @covers \Migration\Handler\Manager::getHandler
     * @return void
     */
    public function testGetHandlerEmpty()
    {
        $field = 'someField';
        $handlerConfig = ['class' => 'Migration\Handler\SetValue', 'params' => ['value' => '12']];
        $handler = $this->getMock('Migration\Handler\SetValue', ['setField'], [], '', false);
        $this->objectManager->expects($this->once())->method('create')->will($this->returnValue($handler));
        $handler->expects($this->once())->method('setField')->with($field);
        $this->manager->initHandler($field, $handlerConfig);
        $this->assertEquals(null, $this->manager->getHandler('non_existant_field'));
    }

    /**
     * @throws \Migration\Exception
     * @return void
     */
    public function testInitHandlerEmptyConfig()
    {
        $this->objectManager->expects($this->never())->method('create');
        $this->manager->initHandler('anyfield');
    }

    /**
     * @throws \Migration\Exception
     * @return void
     */
    public function testInitHandlerEmptyClass()
    {
        $this->setExpectedException('Exception', 'Handler class name not specified.');
        $config = ['class' => '', 'params' => ['value' => '12']];
        $this->manager->initHandler('anyfield', $config);
    }

    /**
     * @throws \Migration\Exception
     * @return void
     */
    public function testInitInvalidHandler()
    {
        $handlerConfig = ['class' => 'Migration\Migration', 'params' => ['value' => '12']];
        $invalidHandler = $this->getMock('Migration\Migration', [], [], '', false);
        $this->objectManager->expects($this->once())->method('create')->will($this->returnValue($invalidHandler));
        $this->setExpectedException('\Exception', "'Migration\Migration' is not correct handler.");
        $this->manager->initHandler('somefield', $handlerConfig);
    }
}
