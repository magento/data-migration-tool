<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Step\Map;

/**
 * Volume step test class
 */
class VolumeTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @throws \Migration\Exception
     * @return void
     */
    public function testPerform()
    {
        $progress = $this->createPartialMock(
            \Migration\App\Progress::class,
            ['getProcessedEntities', 'addProcessedEntity']
        );
        $progress->expects($this->once())->method('getProcessedEntities')->will($this->returnValue([]));
        $progress->expects($this->any())->method('addProcessedEntity');

        $helper = \Migration\TestFramework\Helper::getInstance();
        $objectManager = $helper->getObjectManager();
        $objectManager->get(\Migration\Config::class)
            ->init(dirname(__DIR__) . '/../_files/' . $helper->getFixturePrefix() . 'config.xml');
        $logManager = $objectManager->create(\Migration\Logger\Manager::class);
        $logger = $objectManager->create(\Migration\Logger\Logger::class);
        $config = $objectManager->get(\Migration\Config::class);
        /** @var \Migration\Logger\Manager $logManager */
        $logManager->process(\Migration\Logger\Manager::LOG_LEVEL_ERROR);

        $data = $objectManager->create(
            \Migration\Step\Map\Data::class,
            [
                'logger' => $logger,
                'config' => $config,
                'progress' => $progress
            ]
        );
        $volume = $objectManager->create(
            \Migration\Step\Map\Volume::class,
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
