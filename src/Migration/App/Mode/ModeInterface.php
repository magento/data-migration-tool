<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\App\Mode;

/**
 * Interface ModeInterface
 */
interface ModeInterface
{
    /**
     * Run tool in particular mode
     *
     * @return bool
     */
    public function run();
}
