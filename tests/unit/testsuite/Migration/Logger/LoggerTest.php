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
}
