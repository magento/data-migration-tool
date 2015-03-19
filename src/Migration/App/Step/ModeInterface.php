<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\App\Step;

/**
 * Interface ModeInterface
 */
interface ModeInterface
{
    /**
     * Run tool in particular mode
     *
     * @param array $steps
     * @return void
     */
    public function run(array $steps);
}
