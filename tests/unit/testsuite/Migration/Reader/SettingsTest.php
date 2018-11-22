<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Reader;

/**
 * Class SettingsTest
 */
class SettingsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Settings
     */
    protected $settings;

    /**
     * @return void
     */
    public function setUp()
    {
        $config = $this->getConfigFile('tests/unit/testsuite/Migration/_files/settings.xml');

        $validationState = $this->getMockBuilder(\Magento\Framework\App\Arguments\ValidationState::class)
            ->disableOriginalConstructor()
            ->setMethods(['isValidationRequired'])
            ->getMock();

        $validationState->expects($this->any())->method('isValidationRequired')->willReturn(true);

        $this->settings = new Settings($config, $validationState);
    }

    /**
     * @param string $configPath
     * @return \Migration\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getConfigFile($configPath)
    {
        /** @var \Migration\Config|\PHPUnit_Framework_MockObject_MockObject $config */
        $config = $this->getMockBuilder(\Migration\Config::class)->disableOriginalConstructor()
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
     * @return void
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
     * @return void
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
     * @return void
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
     * @return void
     */
    public function testGetValueHandler($node, $valueHandler)
    {
        $result = $this->settings->getValueHandler($node);
        $this->assertEquals($valueHandler, $result);
        $result = $this->settings->getValueHandler($node);
        $this->assertEquals($valueHandler, $result);
    }

    /**
     * @return void
     */
    public function testNoConfigFile()
    {
        $config = $this->getConfigFile('invalid_file_name');
        $this->expectException(\Migration\Exception::class);
        $this->expectExceptionMessage('Invalid map filename:');

        $validationState = $this->getMockBuilder(\Magento\Framework\App\Arguments\ValidationState::class)
            ->disableOriginalConstructor()
            ->setMethods(['isValidationRequired'])
            ->getMock();

        $validationState->expects($this->any())->method('isValidationRequired')->willReturn(true);

        new Settings($config, $validationState);
    }

    /**
     * @return void
     */
    public function testInvalidConfigFile()
    {
        $config = $this->getConfigFile('tests/unit/testsuite/Migration/_files/settings-invalid.xml');
        $this->expectException(\Migration\Exception::class);
        $this->expectExceptionMessage('XML file is invalid.');

        $validationState = $this->getMockBuilder(\Magento\Framework\App\Arguments\ValidationState::class)
            ->disableOriginalConstructor()
            ->setMethods(['isValidationRequired'])
            ->getMock();

        $validationState->expects($this->any())->method('isValidationRequired')->willReturn(true);

        new Settings($config, $validationState);
    }
}
