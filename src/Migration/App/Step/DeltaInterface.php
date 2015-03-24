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
     * Perform delta migration
     *
     * @return bool
     */
    public function delta();

    /**
     * Setup triggers
     *
     * @return bool
     */
    public function setUpChangeLog();
}
