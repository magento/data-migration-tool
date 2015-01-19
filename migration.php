<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$magentoDir = require __DIR__ . '/etc/magento_path.php';
require_once "{$magentoDir}/app/autoload.php";
use Magento\Framework\App\Bootstrap;

$params = [];
$bootstrap = Bootstrap::create($magentoDir, $params);
/** @var Migration\Magento\Migration $application */
$application = $bootstrap->createApplication('Migration\Magento\Migration', ['entryPoint' => basename(__FILE__)]);
$bootstrap->run($application);
