<?php
/**
 * @copyright Copyright (c) 2015 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Migration;

class MigrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Migration
     */
    protected $migration;

    /**
     * @var \Magento\Framework\App\Console\Response|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $response;

    protected function setUp()
    {
        $this->response = $this->getMock('Magento\Framework\App\Console\Response', [], [], '', false);
        $this->migration = new Migration($this->response);
    }

    public function testLaunch()
    {
        $result = $this->migration->launch();
        $this->assertEquals($this->response, $result);
    }

    public function testCatchException()
    {
        /** @var \Magento\Framework\App\Bootstrap|\PHPUnit_Framework_TestCase $bootstrap */
        $bootstrap = $this->getMock('Magento\Framework\App\Bootstrap', [], [], '', false);
        /** @var \Exception|\PHPUnit_Framework_TestCase $exception */
        $exception = $this->getMock('\Exception', [], [], '', false);
        $result = $this->migration->catchException($bootstrap, $exception);
        $this->assertTrue($result);
    }
}
