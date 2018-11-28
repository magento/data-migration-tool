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
	PRIMARY KEY (`value_id`),
	UNIQUE INDEX `UNQ_E8AB433B9ACB00343ABB312AD2FAB087` (`entity_id`, `all_groups`, `customer_group_id`, `qty`, `website_id`),
	INDEX `CATALOG_PRODUCT_ENTITY_TIER_PRICE_CUSTOMER_GROUP_ID` (`customer_group_id`),
	INDEX `CATALOG_PRODUCT_ENTITY_TIER_PRICE_WEBSITE_ID` (`website_id`)
)
COMMENT='Catalog Product Tier Price Attribute Backend Table'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=0
;
