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
class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Migration\Step\Eav\Data;
     */
    protected $eav;


    public function setUp()
    {
        $objectManager = \Migration\TestFramework\Helper::getInstance()->getObjectManager();
        $objectManager->get('\Migration\Config')->init(dirname(__DIR__) . '/../_files/config.xml');
        $initialData = $objectManager->get('Migration\Step\Eav\InitialData');
        $this->eav = $objectManager->create(
            'Migration\Step\Eav\Data',
            [
                'initialData' => $initialData,
            ]
        );
    }

    public function testPerform()
    {
        $this->assertTrue($this->eav->perform());
    }
}
