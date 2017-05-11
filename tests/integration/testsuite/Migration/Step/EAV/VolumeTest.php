<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Eav;

/**
 * Eav step test
 * @dbFixture eav
 */
class VolumeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Migration\Step\Eav\Data;
     */
    protected $data;

    /**
     * @var \Migration\Step\Eav\Volume;
     */
    protected $volume;

    /**
     * @return void
     */
    public function setUp()
    {
        $objectManager = \Migration\TestFramework\Helper::getInstance()->getObjectManager();
        $objectManager->get('\Migration\Config')->init(dirname(__DIR__) . '/../_files/config.xml');
        $initialData = $objectManager->get('Migration\Step\Eav\InitialData');
        $ignoredAttributes = $objectManager->get('Migration\Step\Eav\Model\IgnoredAttributes');
        $this->data = $objectManager->create(
            'Migration\Step\Eav\Data',
            [
                'initialData' => $initialData,
            ]
        );
        $this->volume = $objectManager->create(
            'Migration\Step\Eav\Volume',
            [
                'initialData' => $initialData,
                'ignoredAttributes' => $ignoredAttributes
            ]
        );
    }

    /**
     * @return void
     */
    public function testPerform()
    {
        $this->assertTrue($this->data->perform());
        $this->assertTrue($this->volume->perform());
    }
}
