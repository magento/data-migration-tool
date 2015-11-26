<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Step\Map;

/**
 * Volume step test class
 */
class VolumeTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @throws \Migration\Exception
     * @return void
     */
    public function testPerform()
    {
        $progress = $this->getMock(
            'Migration\App\Progress',
            ['getProcessedEntities', 'addProcessedEntity'],
            [],
            '',
            false
        );
        $progress->expects($this->once())->method('getProcessedEntities')->will($this->returnValue([]));
        $progress->expects($this->any())->method('addProcessedEntity');

        $helper = \Migration\TestFramework\Helper::getInstance();
        $objectManager = $helper->getObjectManager();
        $objectManager->get('\Migration\Config')
            ->init(dirname(__DIR__) . '/../_files/' . $helper->getFixturePrefix() . 'config.xml');
        $logManager = $objectManager->create('\Migration\Logger\Manager');
        $logger = $objectManager->create('\Migration\Logger\Logger');
        $config = $objectManager->get('\Migration\Config');
        /** @var \Migration\Logger\Manager $logManager */
        $logManager->process(\Migration\Logger\Manager::LOG_LEVEL_ERROR);

        $data = $objectManager->create(
            '\Migration\Step\Map\Data',
            [
                'logger' => $logger,
                'config' => $config,
                'progress' => $progress
            ]
        );
        $volume = $objectManager->create(
            '\Migration\Step\Map\Volume',
            [
                'logger' => $logger,
                'config' => $config,
            ]
        );
        ob_start();
        $data->perform();
        $isSuccess = $volume->perform();
        ob_end_clean();

        $this->assertTrue($isSuccess);
    }
}
