<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Mode;

/**
 * Class Settings
 */
class Settings implements \Migration\App\Mode\ModeInterface
{

    /**
     * {@inheritdoc}
     */
    public function helpUsage()
    {
        return <<<USAGE

Settings mode usage information:

Migrates store settings
USAGE;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        return false;
    }
}
