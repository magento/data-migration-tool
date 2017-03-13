<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\App\Utility\Files;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\DirSearch;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\View\Design\Theme\ThemePackageList;
use Magento\Framework\View\Design\Theme\ThemePackageFactory;

require __DIR__ . '/autoload.php';

$componentRegistrar = new ComponentRegistrar();
$dirSearch = new DirSearch($componentRegistrar, new ReadFactory(new DriverPool()));
$themePackageList = new ThemePackageList($componentRegistrar, new ThemePackageFactory());
\Magento\Framework\App\Utility\Files::setInstance(
    new Files($componentRegistrar, $dirSearch, $themePackageList)
);

error_reporting(E_ALL);
ini_set('display_errors', 1);
