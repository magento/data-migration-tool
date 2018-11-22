<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Logger;

class LoggerTest extends \PHPUnit\Framework\TestCase
{
    /** @var Logger */
    protected $logger;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->logger = new Logger();
    }

    /**
     * @return void
     */
    public function testGetName()
    {
        $someName = 'Some name';
        $logger = new Logger($someName);
        $this->assertEquals($someName, $logger->getName());
    }

    /**
     * @covers Migration\Logger\Logger::addRecord
     * @covers Migration\Logger\Logger::getMessages
     * @return void
     */
    public function testAddRecord()
    {
        $infoMessage = 'info1';
        $errorMessage = 'error1';
        $consoleHandler = $this->getMockBuilder(\Migration\Logger\ConsoleHandler::class)
            ->disableOriginalConstructor()
            ->setMethods(['handle'])
            ->getMock();
        $consoleHandler->expects($this->any())->method('handle')->will($this->returnValue(true));
        $this->logger->pushHandler($consoleHandler);
        $this->logger->addRecord(\Monolog\Logger::INFO, $infoMessage);
        $this->logger->addRecord(\Monolog\Logger::ERROR, $errorMessage);
        $messages = \Migration\Logger\Logger::getMessages();
        $this->assertEquals($infoMessage, $messages[\Monolog\Logger::INFO][0]);
        $this->assertEquals($errorMessage, $messages[\Monolog\Logger::ERROR][0]);
        $this->logger->clearMessages();
        $this->assertEmpty(\Migration\Logger\Logger::getMessages());
    }
}
