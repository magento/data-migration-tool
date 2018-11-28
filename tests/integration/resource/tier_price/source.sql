/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

CREATE TABLE `catalog_product_entity_tier_price` (
	`value_id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'Value ID',
	`entity_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Entity ID',
	`all_groups` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Is Applicable To All Customer Groups',
	`customer_group_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Customer Group ID',
	`qty` DECIMAL(12,4) NOT NULL DEFAULT '1.0000' COMMENT 'QTY',
	`value` DECIMAL(12,4) NOT NULL DEFAULT '0.0000' COMMENT 'Value',
	`website_id` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Website ID',
	`custom_field` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Website ID',
	PRIMARY KEY (`value_id`),
	UNIQUE INDEX `E8AB433B9ACB00343ABB312AD2FAB087` (`entity_id`, `all_groups`, `customer_group_id`, `qty`, `website_id`),
	INDEX `IDX_CATALOG_PRODUCT_ENTITY_TIER_PRICE_ENTITY_ID` (`entity_id`),
	INDEX `IDX_CATALOG_PRODUCT_ENTITY_TIER_PRICE_CUSTOMER_GROUP_ID` (`customer_group_id`),
	INDEX `IDX_CATALOG_PRODUCT_ENTITY_TIER_PRICE_WEBSITE_ID` (`website_id`)
)
COMMENT='Catalog Product Tier Price Attribute Backend Table'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=0
;

/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
INSERT INTO `catalog_product_entity_tier_price` (`entity_id`, `all_groups`, `customer_group_id`, `qty`, `value`, `website_id`) VALUES ('1', '0', '1', '1.0000', '15.0000', '0');
INSERT INTO `catalog_product_entity_tier_price` (`entity_id`, `all_groups`, `customer_group_id`, `qty`, `value`, `website_id`) VALUES ('6', '0', '2', '3.0000', '85.0000', '0');
/*!40101 SET SQL_MODE=IFNULL(@OLD_INSERT_SQL_MODE,'') */;

CREATE TABLE `catalog_product_entity_group_price` (
	`value_id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'Value ID',
	`entity_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Entity ID',
	`all_groups` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Is Applicable To All Customer Groups',
	`customer_group_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Customer Group ID',
	`value` DECIMAL(12,4) NOT NULL DEFAULT '0.0000' COMMENT 'Value',
	`website_id` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Website ID',
	PRIMARY KEY (`value_id`),
	UNIQUE INDEX `CC12C83765B562314470A24F2BDD0F36` (`entity_id`, `all_groups`, `customer_group_id`, `website_id`),
	INDEX `IDX_CATALOG_PRODUCT_ENTITY_GROUP_PRICE_ENTITY_ID` (`entity_id`),
	INDEX `IDX_CATALOG_PRODUCT_ENTITY_GROUP_PRICE_CUSTOMER_GROUP_ID` (`customer_group_id`),
	INDEX `IDX_CATALOG_PRODUCT_ENTITY_GROUP_PRICE_WEBSITE_ID` (`website_id`)
)
COMMENT='Catalog Product Group Price Attribute Backend Table'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=0
;

/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
INSERT INTO `catalog_product_entity_group_price` (`entity_id`, `all_groups`, `customer_group_id`, `value`, `website_id`) VALUES ('1', '0', '1', '10.0000', '0');
INSERT INTO `catalog_product_entity_group_price` (`entity_id`, `all_groups`, `customer_group_id`, `value`, `website_id`) VALUES ('6', '0', '3', '95.0000', '0');
/*!40101 SET SQL_MODE=IFNULL(@OLD_INSERT_SQL_MODE,'') */;
