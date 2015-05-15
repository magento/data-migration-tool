<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\TestFramework;

class QuietLogHandler extends \Monolog\Handler\AbstractProcessingHandler
{
    /**
     * {@inheritdoc}
     */
    protected function write(array $record)
    {
        return;
    }
}
