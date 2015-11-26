<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Logger;

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var ConsoleHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $consoleHandler;

    /**
     * @var \Migration\Logger\FileHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fileHandler;

    /**
     * @var \Migration\Logger\MessageFormatter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageFormatter;

    /**
     * @var \Migration\Logger\MessageProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageProcessor;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->logger = $this->getMock(
            'Migration\Logger\Logger',
            ['pushHandler', 'pushProcessor', 'error'],
            [],
            '',
            false
        );
        $this->consoleHandler = $this->getMock(
            'Migration\Logger\ConsoleHandler',
            ['setLevel', 'setFormatter'],
            [],
            '',
            false
        );
        $this->fileHandler = $this->getMock(
            'Migration\Logger\FileHandler',
            ['setLevel', 'setFormatter'],
            [],
            '',
            false
        );
        $this->messageFormatter = $this->getMock('Migration\Logger\MessageFormatter', [], [], '', false);
        $this->messageProcessor = $this->getMock('Migration\Logger\MessageProcessor', [], [], '', false);
        $this->manager = new Manager(
            $this->logger,
            $this->consoleHandler,
            $this->fileHandler,
            $this->messageFormatter,
            $this->messageProcessor
        );
    }

    /**
     * @return array
     */
    public function dataProviderProcessSuccess()
    {
        return [
            ['logLevel' => 'info', 'logLevelCode' => 200],
            ['logLevel' => 'debug', 'logLevelCode' => 100],
            ['logLevel' => 'ERROR', 'logLevelCode' => 400],
            ['logLevel' => 'InFo', 'logLevelCode' => 200],
            ['logLevel' => 'Debug', 'logLevelCode' => 100],
        ];
    }

    /**
     * @param string $logLevel
     * @param int $logLevelCode
     * @dataProvider dataProviderProcessSuccess
     * @return void
     */
    public function testProcessSuccess($logLevel, $logLevelCode)
    {
        $this->logger->expects($this->any())->method('pushHandler')->willReturnSelf();
        $this->logger->expects($this->once())->method('pushProcessor')->with([$this->messageProcessor, 'setExtra'])
            ->willReturnSelf();
        $this->consoleHandler->expects($this->once())->method('setLevel')->willReturnSelf($logLevelCode);
        $this->consoleHandler->expects($this->once())->method('setFormatter')->with($this->messageFormatter)
            ->willReturnSelf();
        $this->fileHandler->expects($this->once())->method('setLevel')->willReturnSelf();
        $this->fileHandler->expects($this->once())->method('setFormatter')->with($this->messageFormatter)
            ->willReturnSelf();
        $this->manager->process($logLevel);
    }

    /**
     * @return array
     */
    public function dataProviderProcessInvalidLevel()
    {
        return [
            ['logLevel' => 'invalid'],
            ['logLevel' => 200]
        ];
    }

    /**
     * @param string $logLevel
     * @dataProvider dataProviderProcessInvalidLevel
     * @return void
     */
    public function testProcessInvalidLevel($logLevel)
    {
        $this->setExpectedException('Migration\Exception', "Invalid log level '$logLevel' provided.");
        $this->manager->process($logLevel);
    }
}
