<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\App\Step;

/**
 * Interface StageInterface
 */
interface StageInterface
{
    /**
     * Perform the stage
     *
     * @return bool
     */
    public function perform();
}
