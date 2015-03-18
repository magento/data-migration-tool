<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Step\Volume;

/**
 * Volume step test class
 */
class MapTest extends \PHPUnit_Framework_TestCase
{

    public function testPerform()
    {
        $objectManager = \Migration\TestFramework\Helper::getInstance()->getObjectManager();
        $objectManager->get('\Migration\Config')->init(dirname(__DIR__) . '/../_files/config.xml');
        $logManager = $objectManager->create('\Migration\Logger\Manager');
        $integrityMap = $objectManager->create('\Migration\Step\Integrity\Map');
        $runMap = $objectManager->create('\Migration\Step\Run\Map');
        $volume = $objectManager->create('\Migration\Step\Volume\Map');
        $logger = $objectManager->create('\Migration\Logger\Logger');
        $mapReader = $objectManager->create('\Migration\MapReader\MapReaderMain');
        $config = $objectManager->get('\Migration\Config');
        /** @var \Migration\Logger\Manager $logManager */
        $logManager->process(\Migration\Logger\Manager::LOG_LEVEL_NONE);

        ob_start();
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
