<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Logger;

class ConsoleHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConsoleHandler
     */
    protected $consoleHandler;

    protected function setUp()
    {
        $this->consoleHandler = new ConsoleHandler();
    }

    /**
     * @return array
     */
    public function dataProviderHandleSuccess()
    {
        return [
            ['recordLevel' => 200, 'handlerLevel' => 'infO'],
            ['recordLevel' => 100, 'handlerLevel' => 'deBug'],
            ['recordLevel' => 200, 'handlerLevel' => 'debug'],
            ['recordLevel' => 200, 'handlerLevel' => 200],
            ['recordLevel' => 100, 'handlerLevel' => 100],
            ['recordLevel' => 200, 'handlerLevel' => 100],
        ];
    }

    /**
     * @param string $recordLevel
     * @param string|int $handlerLevel
     * @dataProvider dataProviderHandleSuccess
     */
    public function testHandleSuccess($recordLevel, $handlerLevel)
    {
        $message = 'Success message';
        $record = ['message' => $message, 'level' => $recordLevel];
        $this->consoleHandler->setLevel($handlerLevel);
        ob_start();
        $result = $this->consoleHandler->handle($record);
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertTrue($result);
        $this->assertEquals("Success message" . PHP_EOL, $output);
    }

    /**
     * @return array
     */
    public function dataProviderHandleError()
    {
        return [
            ['recordLevel' => 100, 'handlerLevel' => 200],
            ['recordLevel' => 100, 'handlerLevel' => 'info']
        ];
    }

    /**
     * @param string $recordLevel
     * @param string|int $handlerLevel
     * @dataProvider dataProviderHandleError
     */
    public function testHandleError($recordLevel, $handlerLevel)
    {
        $message = 'Error message';
        $record = ['message' => $message, 'level' => $recordLevel];
        $this->consoleHandler->setLevel($handlerLevel);
        $result = $this->consoleHandler->handle($record);
        $this->assertFalse($result);
    }
}
