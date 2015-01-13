<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

$magentoDir = require __DIR__ . '/../../../etc/magento_path.php';
require_once $magentoDir . '/app/autoload.php';
$testsBaseDir = dirname(__DIR__);

$vendorDir = require $magentoDir . '/app/etc/vendor_path.php';
$vendorAutoload = require $magentoDir . "/{$vendorDir}/autoload.php";
$vendorAutoload->add('Migration\\Test\\', "{$testsBaseDir}/testsuite/Migration");
