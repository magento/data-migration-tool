<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Migration\Logger;

class LoggerTest extends \PHPUnit_Framework_TestCase
{
    /** @var Logger */
    protected $logger;

    protected function setUp()
    {
        $this->logger = new Logger();
    }

    public function testGetName()
    {
        $someName = 'Some name';
        $logger = new Logger($someName);
        $this->assertEquals($someName, $logger->getName());
    }

    /**
     * @covers Migration\Logger\Logger::addRecord
     * @covers Migration\Logger\Logger::getMessages
     */
    public function testAddRecord()
    {
        $infoMessage = 'info1';
        $errorMessage = 'error1';
        $this->logger->addRecord(\Monolog\Logger::INFO, $infoMessage);
        $this->logger->addRecord(\Monolog\Logger::ERROR, $errorMessage);
        $messages = \Migration\Logger\Logger::getMessages();
        $this->assertEquals($infoMessage, $messages[\Monolog\Logger::INFO][0]);
        $this->assertEquals($errorMessage, $messages[\Monolog\Logger::ERROR][0]);
    }
}
