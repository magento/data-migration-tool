<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use \Magento\Framework\App\Filesystem\DirectoryList;

$baseDir = require __DIR__ . '/../../../etc/magento_path.php';
require $baseDir . '/app/autoload.php';
require $baseDir . '/vendor/squizlabs/php_codesniffer/autoload.php';
$testsBaseDir = $baseDir . '/dev/tests/static';
$testsBaseDirMigration = $baseDir . '/vendor/magento/data-migration-tool/tests/static';
$autoloadWrapper = \Magento\Framework\Autoload\AutoloaderRegistry::getAutoloader();
$autoloadWrapper->addPsr4('Magento\\', $testsBaseDir . '/testsuite/Magento/');
$autoloadWrapper->addPsr4('Magento\\', $testsBaseDirMigration . '/testsuite/Migration/');
$autoloadWrapper->addPsr4(
    'Magento\\TestFramework\\',
    [
        $testsBaseDir . '/framework/Magento/TestFramework/',
        $testsBaseDir . '/../integration/framework/Magento/TestFramework/',
    ]
);
$autoloadWrapper->addPsr4('Magento\\CodeMessDetector\\', $testsBaseDir . '/framework/Magento/CodeMessDetector');

$generatedCode = DirectoryList::getDefaultConfig()[DirectoryList::GENERATED_CODE][DirectoryList::PATH];
$autoloadWrapper->addPsr4('Magento\\', $baseDir . '/' . $generatedCode . '/Magento/');
