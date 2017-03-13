<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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

    /**
     * @throws Exception
     * @return void
     */
    protected function setUp()
    {
        $validationState = $this->getMockBuilder('Magento\Framework\App\Arguments\ValidationState')
            ->disableOriginalConstructor()
            ->setMethods(['isValidationRequired'])
            ->getMock();

        $validationState->expects($this->any())->method('isValidationRequired')->willReturn(true);

        $this->config = new Config($validationState);
        $this->config->init(realpath(__DIR__ . '/_files/test-config.xml'));
    }

    /**
     * @return void
     */
    public function testDefaultConfigFile()
    {
        $this->assertNotEmpty($this->config->getOption('map_file'));
    }

    /**
     * @throws Exception
     * @return void
     */
    public function testInvalidConfigFile()
    {
        $this->setExpectedException('Migration\Exception', 'Invalid config filename: non-existent.xml');

        $validationState = $this->getMockBuilder('Magento\Framework\App\Arguments\ValidationState')
            ->disableOriginalConstructor()
            ->setMethods(['isValidationRequired'])
            ->getMock();

        $validationState->expects($this->any())->method('isValidationRequired')->willReturn(true);

        $config = new Config($validationState);
        $config->init('non-existent.xml');
    }

    /**
     * @throws Exception
     * @return void
     */
    public function testInvalidXml()
    {
        $this->setExpectedException('Migration\Exception', 'XML file is invalid');

        $validationState = $this->getMockBuilder('Magento\Framework\App\Arguments\ValidationState')
            ->disableOriginalConstructor()
            ->setMethods(['isValidationRequired'])
            ->getMock();

        $validationState->expects($this->any())->method('isValidationRequired')->willReturn(true);

        $config = new Config($validationState);
        $config->init(__DIR__ . '/_files/invalid-config.xml');
    }

    /**
     * @return void
     */
    public function testGetSteps()
    {
        $steps = [
            'Step1' => [
                'integrity' => 'Migration\Step\SomeStep\Integrity',
                'volume' => 'Migration\Step\SomeStep\Volume'
            ],
            'Step2' => [
                'integrity' => 'Migration\Step\SomeStep\Integrity',
                'volume' => 'Migration\Step\SomeStep\Volume'
            ]
        ];
        $this->assertEquals($steps, $this->config->getSteps('data'));
    }

    /**
     * @return void
     */
    public function testGetStep()
    {
        $step = ['delta' => 'Migration\Step\SomeStep\Integrity'];
        $this->assertEquals($step, $this->config->getStep('delta', 'Step1'));
    }

    /**
     * @return void
     */
    public function testGetSource()
    {
        $source = [
            'type' => 'database',
            'database' => [
                'host' => 'localhost',
                'user' => 'root',
                'name' => 'magento1'
            ]
        ];
        $this->assertEquals($source, $this->config->getSource());
    }

    /**
     * @return void
     */
    public function testGetDestination()
    {
        $destination = [
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

    /**
     * @return void
     */
    public function testGetOption()
    {
        $this->assertEquals('map-file.xml', $this->config->getOption('map_file'));
        $this->assertEquals('etc/settings.xml', $this->config->getOption('settings_map_file'));
        $this->assertEquals('100', $this->config->getOption('bulk_size'));
        $this->assertEquals('custom_option_value', $this->config->getOption('custom_option'));
        $this->assertEquals('map-sales.xml', $this->config->getOption('sales_order_map_file'));
    }
}
