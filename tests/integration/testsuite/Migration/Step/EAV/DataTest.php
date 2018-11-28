<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Eav;

/**
 * Eav step test
 * @dbFixture eav
 */
class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Migration\Step\Eav\Data;
     */
    protected $eav;

    /**
     * @return void
     */
    public function setUp()
    {
        $objectManager = \Migration\TestFramework\Helper::getInstance()->getObjectManager();
        $objectManager->get(\Migration\Config::class)->init(dirname(__DIR__) . '/../_files/config.xml');
        $initialData = $objectManager->get(\Migration\Step\Eav\InitialData::class);
        $this->eav = $objectManager->create(
            \Migration\Step\Eav\Data::class,
            [
                'initialData' => $initialData,
            ]
        );
    }

    /**
     * @return void
     */
    public function testPerform()
    {
        $this->assertTrue($this->eav->perform());
    }
}
