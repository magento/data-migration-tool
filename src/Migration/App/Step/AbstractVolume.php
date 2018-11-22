<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\App\Step;

use Migration\Logger\Logger;

/**
 * Class AbstractVolume
 */
abstract class AbstractVolume implements StageInterface
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @param Logger $logger
     */
    public function __construct(
        Logger $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * Process errors
     *
     * @param int $errorLevel
     * @return bool
     */
    protected function checkForErrors($errorLevel = Logger::WARNING)
    {
        foreach ($this->errors as $error) {
            $this->logger->addRecord($errorLevel, $error);
        }
        return empty($this->errors);
    }
}
