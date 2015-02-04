<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Step\Integrity;

/**
 * Integrity step test class
 */
class IntegrityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Migration\Step\Integrity $integrity
     */
    protected $integrity;

    protected function setUp()
    {
        $helper = \Migration\TestFramework\Helper::getInstance();
        $objectManager = $helper->getObjectManager();
        $objectManager->get('\Migration\Config')->init($helper->getConfigPath());
        $logManager = $objectManager->get('\Migration\Logger\Manager');
        $logManager->process(\Migration\Logger\Manager::LOG_LEVEL_NONE);
        $this->integrity = $objectManager->get('\Migration\Step\Integrity');
    }

    public function testRun()
    {
        $this->integrity->run();
        $messages = \Migration\Logger\Logger::getMessages();
        $errorDocumentDestination = "/.*The documents bellow are not exist in the destination resource:\n"
            ."custom_extension_source$/";
        $errorDocumentSource = "/.*The documents bellow are not exist in the source resource:\n"
            ."custom_extension_destination$/";
        $errorFieldDestination = "/.*In the documents bellow fields are not exist in the destination resource:\n"
            ."Document name:catalog_product_entity; Fields:custom_extension_field_source$/";
        $errorFieldSource = "/.*In the documents bellow fields are not exist in the source resource:\n"
            ."Document name:catalog_product_entity; Fields:custom_extension_field_destination$/";
        $this->assertTrue(array_key_exists(\Monolog\Logger::ERROR, $messages));
        $foundErrors = 0;
        foreach ($messages[\Monolog\Logger::ERROR] as $message) {
            if (preg_match($errorDocumentDestination, $message)) {
                ++$foundErrors;
            }
            if (preg_match($errorDocumentSource, $message)) {
                ++$foundErrors;
            }
            if (preg_match($errorFieldDestination, $message)) {
                ++$foundErrors;
            }
            if (preg_match($errorFieldSource, $message)) {
                ++$foundErrors;
            }
        }
        $this->assertEquals(4, $foundErrors);
    }
}
