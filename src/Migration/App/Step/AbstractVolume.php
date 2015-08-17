<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * @return bool
     */
    protected function checkForErrors()
    {
        foreach ($this->errors as $error) {
            $this->logger->warning($error);
        }
        return empty($this->errors);
    }
}
