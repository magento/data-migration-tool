<?php
/**
 * @copyright Copyright (c) 2015 X.commerce, Inc. (http://www.magentocommerce.com)
 */

$magentoDir = require __DIR__ . '/etc/magento_path.php';
require_once "{$magentoDir}/app/autoload.php";
use Magento\Framework\App\Bootstrap;
include __DIR__ . '/src/Migration.php';

$params = [];
$bootstrap = Bootstrap::create($magentoDir, $params);
/** @var Migration\Migration $application */
$application = $bootstrap->createApplication('Migration\Migration');
$bootstrap->run($application);
