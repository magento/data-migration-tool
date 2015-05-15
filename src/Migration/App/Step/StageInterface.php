<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
