<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

use Migration\Exception;
use Migration\App\Step\StageInterface;

/**
 * Class DatabaseStage
 */
abstract class DatabaseStage implements StageInterface
{
    const SOURCE_TYPE = 'database';

    /**
     * @var \Migration\Config
     */
    protected $configReader;

    /**
     * @param \Migration\Config $config
     * @throws Exception
     */
    public function __construct(
        \Migration\Config $config
    ) {
        $this->configReader = $config;
        if (!$this->canStart()) {
            throw new Exception('Can not execute step');
        }
    }

    /**
     * Check Step can be started
     *
     * @return bool
     */
    protected function canStart()
    {
        return $this->configReader->getSource()['type'] == self::SOURCE_TYPE;
    }
}
