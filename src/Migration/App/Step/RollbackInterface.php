<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\App\Step;

/**
 * Interface RollbackInterface
 */
interface RollbackInterface
{
    /**
     * Perform rollback
     *
     * @return mixed
     */
    public function rollback();
}
