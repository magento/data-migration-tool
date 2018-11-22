<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\TestFramework;

/**
 * Class ProgressBar
 */
class ProgressBar extends \Migration\App\ProgressBar\LogLevelProcessor
{
    /**
     * @inheritdoc
     */
    public function start($max = null, $forceLogLevel = false)
    {
    }

    /**
     * @inheritdoc
     */
    public function finish($forceLogLevel = false)
    {
    }

    /**
     * @inheritdoc
     */
    public function advance($forceLogLevel = false)
    {
    }
}
