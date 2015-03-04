<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration;

/**
 * Class ConfigTest
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config
     */
    protected $config;

    protected function setUp()
    {
        $this->config = new Config();
        $this->config->init(realpath(__DIR__ . '/_files/test-config.xml'));
    }

    public function testDefaultConfigFile()
    {
        $this->assertNotEmpty($this->config->getOption('map_file'));
    }

    public function testInvalidConfigFile()
    {
        $this->setExpectedException('Exception', 'Invalid config filename: non-existent.xml');
        $config = new Config();
        $config->init('non-existent.xml');
    }

    public function testInvalidXml()
    {
        $this->setExpectedException('Exception', 'XML file is invalid');
        $config = new Config();
        $config->init(__DIR__ . '/_files/invalid-config.xml');
    }

    public function testGetSteps()
    {
        $steps = [
            'Migration\Step\Eav',
            'Migration\Step\Map',
            'Migration\Step\UrlRewrite',
            'Migration\Step\Log'
        ];
        $this->assertEquals($steps, $this->config->getSteps());
    }

    public function testGetSource()
    {
        $source = [
            'version' => '1.14.1.0',
            'type' => 'database',
            'database' => [
                'host' => 'localhost',
                'user' => 'root',
                'name' => 'magento1'
            ]
        ];
        $this->assertEquals($source, $this->config->getSource());
    }

    public function testGetDestination()
    {
        $destination = [
            'version' => '2.0.0.0',
            'type' => 'database',
            'database' => [
                'host' => 'localhost',
                'user' => 'root',
                'name' => 'magento2',
                'password' => '123123q'
            ]
        ];
        $this->assertEquals($destination, $this->config->getDestination());
    }

    public function testGetOption()
    {
        $this->assertEquals('map-file.xml', $this->config->getOption('map_file'));
        $this->assertEquals('settings-map-file.xml', $this->config->getOption('settings_map_file'));
        $this->assertEquals('100', $this->config->getOption('bulk_size'));
        $this->assertEquals('custom_option_value', $this->config->getOption('custom_option'));
    }
}
