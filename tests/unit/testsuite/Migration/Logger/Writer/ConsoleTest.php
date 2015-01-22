<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Migration\Logger\Writer;

class ConsoleTest extends \PHPUnit_Framework_TestCase
{
    /** @var Console */
    protected $console;

    /** @var \Zend\Console\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $consoleAdapter;

    protected function setUp()
    {
        /** @var \Migration\Logger\Writer\Console\Creator|\PHPUnit_Framework_MockObject_MockObject $сonsoleCreator */
        $сonsoleCreator = $this->getMock('Migration\Logger\Writer\Console\Creator', [], [], '', false);
        $this->consoleAdapter = $this->getMock('Zend\Console\Adapter\Posix', [], [], '', false);
        $сonsoleCreator->expects($this->once())->method('create')->will($this->returnValue($this->consoleAdapter));
        $this->console = new Console($сonsoleCreator);
    }

    public function testWrite()
    {
        $message = 'it is some message';
        $this->consoleAdapter->expects($this->once())->method('writeLine')->with($message);
        $this->console->write($message, 'DEBUG');
    }
    public function testWriteSuccess()
    {
        $this->consoleAdapter->expects($this->once())->method('writeLine')->with('[SUCCESS]: Success message');
        $this->console->writeSuccess('Success message');
    }

    public function testWriteError()
    {
        $this->consoleAdapter->expects($this->once())->method('writeLine')->with('[ERROR]: Error message');
        $this->console->writeError('Error message');
    }

    public function testLoggingLevel()
    {
        $loggingLevel = 'DEBUG';
        $this->console->setLoggingLevel($loggingLevel);
        $this->assertEquals($loggingLevel, $this->console->getLoggingLevel());
    }
}
