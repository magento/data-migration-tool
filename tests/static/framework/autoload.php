<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$magentoDir = require __DIR__ . '/../../../etc/magento_path.php';
require_once $magentoDir . '/app/autoload.php';

$vendorDir = require $magentoDir . '/app/etc/vendor_path.php';
$vendorAutoload = require $magentoDir . "/{$vendorDir}/autoload.php";
$testsBaseDir = "$magentoDir/$vendorDir/magento/data-migration-tool/tests/static";
$vendorAutoload->addPsr4('Migration\\Test\\', "{$testsBaseDir}/testsuite/Migration/");
$vendorAutoload->addPsr4(
    'Magento\\TestFramework\\',
    $magentoDir . '/dev/tests/static/framework/Magento/TestFramework/'
);
