<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Reader;

/**
 * Class SettingsTest
 */
class SettingsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Settings
     */
    protected $settings;

    public function setUp()
    {
        $config = $this->getConfigFile('tests/unit/testsuite/Migration/_files/settings.xml');
        $this->settings = new Settings($config);
    }

    /**
     * @param string $configPath
     * @return \Migration\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getConfigFile($configPath)
    {
        /** @var \Migration\Config|\PHPUnit_Framework_MockObject_MockObject $config */
        $config = $this->getMockBuilder('Migration\Config')->disableOriginalConstructor()
            ->setMethods(['getOption'])->getMock();
        $config->expects($this->once())->method('getOption')->with('settings_map_file')->will(
            $this->returnValue($configPath)
        );
        return $config;
    }

    /**
     * @return array
     */
    public function dataProviderNodesIgnore()
    {
        return [
            ['node' => '', 'isIgnored' => false],
            ['node' => 'path/to/ignore/is/here', 'isIgnored' => true],
            ['node' => 'exact/path/to/ignore', 'isIgnored' => true],
            ['node' => 'exact/path/to/ignore/dummy', 'isIgnored' => false],
            ['node' => 'dummy/path', 'isIgnored' => false]
        ];
    }

    /**
     * @param string $node
     * @param bool $isIgnored
     * @dataProvider dataProviderNodesIgnore
     */
    public function testIsNodeIgnored($node, $isIgnored)
    {
        $result = $this->settings->isNodeIgnored($node);
        $this->assertEquals($isIgnored, $result);
        $result = $this->settings->isNodeIgnored($node);
        $this->assertEquals($isIgnored, $result);
    }

    /**
     * @return array
     */
    public function dataProviderNodeIsMapped()
    {
        return [
            ['node' => '', 'isMapped' => false],
            ['node' => 'path/to/rename', 'isMapped' => true],
            ['node' => 'renamed/path/to/ignore', 'isMapped' => false],
            ['node' => 'some/dummy/path', 'isMapped' => false]

        ];
    }

    /**
     * @param string $node
     * @param bool $isMapped
     * @dataProvider dataProviderNodeIsMapped
     */
    public function testIsNodeMapped($node, $isMapped)
    {
        $result = $this->settings->isNodeMapped($node);
        $this->assertEquals($isMapped, $result);
        $result = $this->settings->isNodeMapped($node);
        $this->assertEquals($isMapped, $result);
    }

    /**
     * @return array
     */
    public function dataProviderNodesMap()
    {
        return [
            ['node' => '', 'nodeMap' => ''],
            ['node' => 'path/to/rename', 'nodeMap' => 'new/path/renamed'],
            ['node' => 'renamed/path/to/ignore', 'nodeMap' => 'renamed/path/to/ignore'],
            ['node' => 'some/dummy/path', 'nodeMap' => 'some/dummy/path']

        ];
    }

    /**
     * @param string $node
     * @param string $nodeMap
     * @dataProvider dataProviderNodesMap
     */
    public function testGetNodeMap($node, $nodeMap)
    {
        $result = $this->settings->getNodeMap($node);
        $this->assertEquals($nodeMap, $result);
        $result = $this->settings->getNodeMap($node);
        $this->assertEquals($nodeMap, $result);
    }

    /**
     * @return array
     */
    public function dataProviderValueHandler()
    {
        return [
            ['node' => '', 'valueHandler' => false],
            ['node' => 'some/key/to/change', 'valueHandler' => ['class' => 'Some\Handler\Class',  'params' => []]],
            ['node' => 'handled/path/to/ignore', 'valueHandler' => false],
            ['node' => 'some/dummy/path', 'valueHandler' => false]

        ];
    }

    /**
     * @param string $node
     * @param string $valueHandler
     * @dataProvider dataProviderValueHandler
     */
    public function testGetValueHandler($node, $valueHandler)
    {
        $result = $this->settings->getValueHandler($node);
        $this->assertEquals($valueHandler, $result);
        $result = $this->settings->getValueHandler($node);
        $this->assertEquals($valueHandler, $result);
    }

    public function testNoConfigFile()
    {
        $config = $this->getConfigFile('invalid_file_name');
        $this->setExpectedException('Migration\Exception', 'Invalid map filename:');
        new Settings($config);
    }

    public function testInvalidConfigFile()
    {
        $config = $this->getConfigFile('tests/unit/testsuite/Migration/_files/settings-invalid.xml');
        $this->setExpectedException('Migration\Exception', 'XML file is invalid.');
        new Settings($config);
    }
}
