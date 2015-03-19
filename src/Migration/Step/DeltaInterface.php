<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Step;

/**
 * Interface StepInterface
 */
interface DeltaInterface
{
    /**
     * Set Up triggers for step tables
     *
     * @return bool
     */
    public function setUpDelta();
}
