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

    public function testAddGetWriter()
    {
        /** @var Writer\Console $writer */
        $writer = $this->getMock('Migration\Logger\Writer\Console', [], [], '', false);
        $this->logger->addWriter($writer);
        $result = $this->logger->getWriters();
        $this->assertEquals([$writer], $result);
    }

    public function testAddGetWriters()
    {
        /** @var Writer\Console $writer */
        $writer1 = $this->getMock('Migration\Logger\Writer\Console', [], [], '', false);
        $writer2 = $this->getMock('Migration\Logger\Writer\Console', [], [], '', false);
        $this->logger->addWriter($writer1);
        $this->logger->addWriter($writer2);
        $result = $this->logger->getWriters();
        $this->assertEquals([$writer1, $writer2], $result);
    }

    public function testLogWrite()
    {
        $message = 'someMessage';
        /** @var Writer\Console $writer */
        $writer = $this->getMock('Migration\Logger\Writer\Console', [], [], '', false);
        $writer->expects($this->once())->method('getLoggingLevel')->will($this->returnValue('INFO'));
        $writer->expects($this->once())->method('write')->with($message, 'INFO');

        $this->logger->addWriter($writer);
        $this->logger->log($message, 'INFO');
    }

    public function testLogSkip()
    {
        $message = 'someMessage';
        /** @var Writer\Console $writer */
        $writer = $this->getMock('Migration\Logger\Writer\Console', [], [], '', false);
        $writer->expects($this->once())->method('getLoggingLevel')->will($this->returnValue('DEBUG'));
        $writer->expects($this->never())->method('write');

        $this->logger->addWriter($writer);
        $this->logger->log($message, 'INFO');
    }

    public function logInfoDataProvider()
    {
        return [
            ['writerLoggingLevel' => 'INFO', 'callWrite' => true],
            ['writerLoggingLevel' => 'DEBUG', 'callWrite' => false],
            ['writerLoggingLevel' => 'NONE', 'callWrite' => false]
        ];
    }

    /**
     * @param $writerLoggingLevel
     * @param $callWrite
     * @dataProvider logInfoDataProvider
     */
    public function testLogInfo($writerLoggingLevel, $callWrite)
    {
        $message = 'someMessage';
        /** @var Writer\Console $writer */
        $writer = $this->getMock('Migration\Logger\Writer\Console', [], [], '', false);
        $writer->expects($this->once())->method('getLoggingLevel')->will($this->returnValue($writerLoggingLevel));
        $callNumberObject = $callWrite ? $this->once() : $this->never();
        $writer->expects($callNumberObject)->method('write')->with($message, 'INFO');

        $this->logger->addWriter($writer);
        $this->logger->logInfo($message);
    }

    public function logDebugDataProvider()
    {
        return [
            ['writerLoggingLevel' => 'INFO', 'callWrite' => true],
            ['writerLoggingLevel' => 'DEBUG', 'callWrite' => true],
            ['writerLoggingLevel' => 'NONE', 'callWrite' => false]
        ];
    }

    /**
     * @param $writerLoggingLevel
     * @param $callWrite
     * @dataProvider logDebugDataProvider
     */
    public function testLogDebug($writerLoggingLevel, $callWrite)
    {
        $message = 'someMessage';
        /** @var Writer\Console $writer */
        $writer = $this->getMock('Migration\Logger\Writer\Console', [], [], '', false);
        $writer->expects($this->once())->method('getLoggingLevel')->will($this->returnValue($writerLoggingLevel));
        $callNumberObject = $callWrite ? $this->once() : $this->never();
        $writer->expects($callNumberObject)->method('write')->with($message, 'DEBUG');

        $this->logger->addWriter($writer);
        $this->logger->logDebug($message);
    }

    public function testLogSuccess()
    {
        $message = 'success msg';
        /** @var Writer\Console $writer */
        $writer = $this->getMock('Migration\Logger\Writer\Console', [], [], '', false);
        $writer->expects($this->once())->method('writeSuccess')->with($message);
        $this->logger->addWriter($writer);
        $this->logger->logSuccess($message);
    }

    public function testLogError()
    {
        $message = 'error msg';
        /** @var Writer\Console $writer */
        $writer = $this->getMock('Migration\Logger\Writer\Console', [], [], '', false);
        $writer->expects($this->once())->method('writeError')->with($message);
        $this->logger->addWriter($writer);
        $this->logger->logError($message);
    }

    public function logLevelValidationDataProvider()
    {
        return [
            'info' => ['logLevel' => 'INFO', 'isValid' => true],
            'debug' => ['logLevel' => 'DEBUG', 'isValid' => true],
            'none' => ['logLevel' => 'NONE', 'isValid' => true],
            'incorrect1' => ['logLevel' => 'Incorrect', 'isValid' => false],
            'incorrect2' => ['logLevel' => 'error', 'isValid' => false]
        ];
    }

    /**
     * @param string $logLevel
     * @param bool $isValid
     * @dataProvider logLevelValidationDataProvider
     */
    public function testIsLogLevelValid($logLevel, $isValid)
    {
        $this->assertEquals($isValid, $this->logger->isLogLevelValid($logLevel));
    }
}
