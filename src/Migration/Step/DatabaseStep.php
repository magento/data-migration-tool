<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

/**
 * Class DatabaseStep
 */
abstract class DatabaseStep extends AbstractStep
{
    const SOURCE_TYPE = 'database';
    /**
     * @var \Migration\Config
     */
    protected $configReader;

    /**
     * @param Progress $progress
     * @param \Migration\Logger\Logger $logger
     * @param \Migration\Config $config
     */
    public function __construct(
        Progress $progress,
        \Migration\Logger\Logger $logger,
        \Migration\Config $config
    ) {
        $this->configReader = $config;
        parent::__construct($progress, $logger);
    }

    /**
     * Check Step can be started
     *
     * @return bool
     */
    public function canStart()
    {
        return $this->configReader->getSource()['type'] == self::SOURCE_TYPE;
    }
}
