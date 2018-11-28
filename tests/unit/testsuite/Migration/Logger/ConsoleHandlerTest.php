<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Logger;

class ConsoleHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ConsoleHandler
     */
    protected $consoleHandler;

    /**
     * @return void
     */
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
            ['recordLevel' => 200, 'handlerLevel' => 100]
        ];
    }

    /**
     * @param string $recordLevel
     * @param string|int $handlerLevel
     * @dataProvider dataProviderHandleSuccess
     * @return void
     */
    public function testHandleSuccess($recordLevel, $handlerLevel)
    {
        $message = 'Success message';
        $extra = ['mode' => 'application mode'];
        $context = [];
        $record = ['message' => $message, 'level' => $recordLevel, 'extra' => $extra, 'context' => $context];
        $this->consoleHandler->setLevel($handlerLevel);
        ob_start();
        $result = $this->consoleHandler->handle($record);
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertFalse($result);
        $this->assertContains('Success message', $output);
    }

    /**
     * @param string $recordLevel
     * @param string|int $handlerLevel
     * @dataProvider dataProviderHandleSuccess
     * @return void
     */
    public function testHandleSuccessWithoutBubble($recordLevel, $handlerLevel)
    {
        $message = 'Success message';
        $extra = ['mode' => 'application mode'];
        $context = [];
        $record = ['message' => $message, 'level' => $recordLevel, 'extra' => $extra, 'context' => $context];
        $this->consoleHandler->setLevel($handlerLevel);
        ob_start();
        $this->consoleHandler->setBubble(false);
        $result = $this->consoleHandler->handle($record);
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertTrue($result);
        $this->assertContains('Success message', $output);
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
     * @return void
     */
    public function testHandleError($recordLevel, $handlerLevel)
    {
        $message = 'Error message';
        $extra = ['mode' => 'application mode'];
        $context = [];
        $record = ['message' => $message, 'level' => $recordLevel, 'extra' => $extra, 'context' => $context];
        $this->consoleHandler->setLevel($handlerLevel);
        $result = $this->consoleHandler->handle($record);
        $this->assertFalse($result);
    }

    /**
     * @return void
     */
    public function testHandleWarning()
    {
        $message = 'Warnin message';
        $extra = ['mode' => 'application mode'];
        $context = [];
        $record = ['message' => $message, 'level' => 300, 'extra' => $extra, 'context' => $context];
        $this->consoleHandler->setLevel(100);
        ob_start();
        $this->consoleHandler->setBubble(false);
        $result = $this->consoleHandler->handle($record);
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertTrue($result);
        $this->assertContains($message, $output);
    }

    /**
     * @return void
     */
    public function testHandleRed()
    {
        $message = 'Colorized message';
        $context = [];
        $record = ['message' => $message, 'level' => 400, 'extra' => [], 'context' => $context];
        $this->consoleHandler->setLevel(100);
        ob_start();
        $result = $this->consoleHandler->handle($record);
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertFalse($result);
        $this->assertContains('Colorized message', $output);
    }
}
