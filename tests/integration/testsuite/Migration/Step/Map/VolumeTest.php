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

    public function testPerform()
    {
        $progress = $this->getMock(
            'Migration\App\Step\Progress',
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
        $integrityMap = $objectManager->create('\Migration\Step\Map\Integrity');
        $runMap = $objectManager->create('\Migration\Step\Map\Migrate', ['progress' => $progress]);
        $volume = $objectManager->create('\Migration\Step\Map\Volume');
        $logger = $objectManager->create('\Migration\Logger\Logger');
        $mapReader = $objectManager->create('\Migration\MapReader\MapReaderMain');
        $config = $objectManager->get('\Migration\Config');
        /** @var \Migration\Logger\Manager $logManager */
        $logManager->process(\Migration\Logger\Manager::LOG_LEVEL_NONE);

        ob_start();
        /**
         * @var \Migration\Step\Map $map
         */
        $map = $objectManager->create(
            '\Migration\Step\Map',
            [
                'integrity' => $integrityMap,
                'run' => $runMap,
                'volume' => $volume,
                'logger' => $logger,
                'map' => $mapReader,
                'config' => $config
            ]
        );
        ob_start();
        $map->run();
        $isSuccess = $map->volumeCheck();
        ob_end_clean();

        $this->assertTrue($isSuccess);
    }
}
