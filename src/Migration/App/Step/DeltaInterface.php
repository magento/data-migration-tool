<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\App\Step;

/**
 * Interface DeltaInterface
 */
interface DeltaInterface
{
    /**
     * Setup delta
     *
     * @return bool
     */
    public function setUpDelta();
}
