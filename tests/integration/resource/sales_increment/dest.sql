/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

DROP TABLE IF EXISTS `sales_sequence_meta`;
CREATE TABLE `sales_sequence_meta` (
	`meta_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Id',
	`entity_type` VARCHAR(32) NOT NULL COMMENT 'Prefix',
	`store_id` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Store Id',
	`sequence_table` VARCHAR(32) NOT NULL COMMENT 'table for sequence',
	PRIMARY KEY (`meta_id`),
	UNIQUE INDEX `SALES_SEQUENCE_META_ENTITY_TYPE_STORE_ID` (`entity_type`, `store_id`)
)
COMMENT='sales_sequence_meta'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
DROP TABLE IF EXISTS `sales_sequence_profile`;
CREATE TABLE `sales_sequence_profile` (
	`profile_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Id',
	`meta_id` INT(10) UNSIGNED NOT NULL COMMENT 'Meta_id',
	`prefix` VARCHAR(32) NULL DEFAULT NULL COMMENT 'Prefix',
	`suffix` VARCHAR(32) NULL DEFAULT NULL COMMENT 'Suffix',
	`start_value` INT(10) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Start value for sequence',
	`step` INT(10) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Step for sequence',
	`max_value` INT(10) UNSIGNED NOT NULL COMMENT 'MaxValue for sequence',
	`warning_value` INT(10) UNSIGNED NOT NULL COMMENT 'WarningValue for sequence',
	`is_active` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'isActive flag',
	PRIMARY KEY (`profile_id`),
	UNIQUE INDEX `SALES_SEQUENCE_PROFILE_META_ID_PREFIX_SUFFIX` (`meta_id`, `prefix`, `suffix`),
	CONSTRAINT `SALES_SEQUENCE_PROFILE_META_ID_SALES_SEQUENCE_META_META_ID` FOREIGN KEY (`meta_id`) REFERENCES `sales_sequence_meta` (`meta_id`) ON DELETE CASCADE
)
COMMENT='sales_sequence_profile'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;

DROP TABLE IF EXISTS `eav_entity_type`;
CREATE TABLE `eav_entity_type` (
	`entity_type_id` SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Entity Type Id',
	`entity_type_code` VARCHAR(50) NOT NULL COMMENT 'Entity Type Code',
	`entity_model` VARCHAR(255) NOT NULL COMMENT 'Entity Model',
	`attribute_model` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Attribute Model',
	`entity_table` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Entity Table',
	`value_table_prefix` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Value Table Prefix',
	`entity_id_field` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Entity Id Field',
	`is_data_sharing` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Defines Is Data Sharing',
	`data_sharing_key` VARCHAR(100) NULL DEFAULT 'default' COMMENT 'Data Sharing Key',
	`default_attribute_set_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Default Attribute Set Id',
	`increment_model` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Increment Model',
	`increment_per_store` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Increment Per Store',
	`increment_pad_length` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '8' COMMENT 'Increment Pad Length',
	`increment_pad_char` VARCHAR(1) NOT NULL DEFAULT '0' COMMENT 'Increment Pad Char',
	`additional_attribute_table` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Additional Attribute Table',
	`entity_attribute_collection` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Entity Attribute Collection',
	PRIMARY KEY (`entity_type_id`),
	INDEX `IDX_EAV_ENTITY_TYPE_ENTITY_TYPE_CODE` (`entity_type_code`)
)
COMMENT='Eav Entity Type'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
INSERT INTO `eav_entity_type` VALUES
(11, 'order', 'Magento\\Sales\\Model\\ResourceModel\\Order', NULL, 'sales_order', '', '', 1, 'default', 11, 'Magento\\Eav\\Model\\Entity\\Increment\\NumericValue', 1, 8, '0', NULL, NULL),
(16, 'invoice', 'Magento\\Sales\\Model\\ResourceModel\\Order\\Invoice', NULL, 'sales_order_entity', '', '', 1, 'default', 16, 'Magento\\Eav\\Model\\Entity\\Increment\\NumericValue', 1, 8, '0', NULL, NULL),
(19, 'shipment', 'Magento\\Sales\\Model\\ResourceModel\\Order\\Shipment', NULL, 'sales_order_entity', '', '', 1, 'default', 19, 'Magento\\Eav\\Model\\Entity\\Increment\\NumericValue', 1, 8, '0', NULL, NULL),
(23, 'creditmemo', 'Magento\\Sales\\Model\\ResourceModel\\Order\\Creditmemo', NULL, 'sales_order_entity', '', '', 1, 'default', 23, 'Magento\\Eav\\Model\\Entity\\Increment\\NumericValue', 1, 8, '0', NULL, NULL),
(26, 'rma_item', 'Magento\\Rma\\Model\\ResourceModel\\Item', 'Magento\\Rma\\Model\\Item\\Attribute', 'magento_rma_item_entity', NULL, NULL, 1, 'default', 27, 'Magento\\Eav\\Model\\Entity\\Increment\\NumericValue', 1, 8, '0', 'magento_rma_item_eav_attribute', NULL);



/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-01-29 19:44:27
