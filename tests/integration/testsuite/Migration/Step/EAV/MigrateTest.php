<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Eav;

/**
 * Eav step test
 * @dbFixture eav
 */
class MigrateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Migration\Step\Eav;
     */
    protected $eav;


    public function setUp()
    {
        $helper = \Migration\TestFramework\Helper::getInstance();
        $objectManager = $helper->getObjectManager();
        $objectManager->get('\Migration\Config')->init(
            dirname(__DIR__) . '/../_files/' . $helper->getFixturePrefix() . 'config.xml'
        );
        $initialData = $objectManager->create('Migration\Step\Eav\InitialData');
        $integrity = $objectManager->create('Migration\Step\Eav\Integrity');
        $migrate = $objectManager->create('Migration\Step\Eav\Migrate');
        $volume = $objectManager->create('Migration\Step\Eav\Volume');
        $this->eav = $objectManager->create(
            'Migration\Step\Eav',
            [
                $initialData,
                $integrity,
                $migrate,
                $volume
            ]
        );
    }

    public function testIntegrity()
    {
        $this->assertTrue($this->eav->integrity());
    }

    public function testRun()
    {
        $this->assertTrue($this->eav->run());
    }

    public function testVolume()
    {
        $this->assertTrue($this->eav->volumeCheck());
    }
}
