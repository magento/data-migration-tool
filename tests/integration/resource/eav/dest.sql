/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

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
INSERT INTO `eav_entity_type` (`entity_type_id`, `entity_type_code`, `entity_model`, `attribute_model`, `entity_table`, `value_table_prefix`, `entity_id_field`, `is_data_sharing`, `data_sharing_key`, `default_attribute_set_id`, `increment_model`, `increment_per_store`, `increment_pad_length`, `increment_pad_char`, `additional_attribute_table`, `entity_attribute_collection`) VALUES (4, 'catalog_product', 'Magento\\Catalog\\Model\\ResourceModel\\Product', 'Magento\\Catalog\\Model\\ResourceModel\\Eav\\Attribute', 'catalog_product_entity', NULL, NULL, 1, 'default', 4, NULL, 0, 8, '0', 'catalog_eav_attribute', 'Magento\\Catalog\\Model\\ResourceModel\\Product\\Attribute\\Collection');
INSERT INTO `eav_entity_type` (`entity_type_id`, `entity_type_code`, `entity_model`, `attribute_model`, `entity_table`, `value_table_prefix`, `entity_id_field`, `is_data_sharing`, `data_sharing_key`, `default_attribute_set_id`, `increment_model`, `increment_per_store`, `increment_pad_length`, `increment_pad_char`, `additional_attribute_table`, `entity_attribute_collection`) VALUES (1, 'customer', 'Magento\\Customer\\Model\\ResourceModel\\Customer', 'Magento\\Customer\\Model\\Attribute', 'customer_entity', NULL, NULL, 1, 'default', 1, 'Magento\\Eav\\Model\\Entity\\Increment\\Numeric', 0, 8, '0', 'customer_eav_attribute', 'Magento\\Customer\\Model\\ResourceModel\\Attribute\\Collection');

DROP TABLE IF EXISTS `eav_attribute`;
CREATE TABLE `eav_attribute` (
	`attribute_id` SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Attribute Id',
	`entity_type_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Entity Type Id',
	`attribute_code` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Attribute Code',
	`attribute_model` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Attribute Model',
	`backend_model` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Backend Model',
	`backend_type` VARCHAR(8) NOT NULL DEFAULT 'static' COMMENT 'Backend Type',
	`backend_table` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Backend Table',
	`frontend_model` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Frontend Model',
	`frontend_input` VARCHAR(50) NULL DEFAULT NULL COMMENT 'Frontend Input',
	`frontend_label` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Frontend Label',
	`frontend_class` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Frontend Class',
	`source_model` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Source Model',
	`is_required` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Defines Is Required',
	`is_user_defined` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Defines Is User Defined',
	`default_value` TEXT NULL COMMENT 'Default Value',
	`is_unique` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Defines Is Unique',
	`note` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Note',
	PRIMARY KEY (`attribute_id`),
	UNIQUE INDEX `UNQ_EAV_ATTRIBUTE_ENTITY_TYPE_ID_ATTRIBUTE_CODE` (`entity_type_id`, `attribute_code`),
	CONSTRAINT `FK_EAV_ATTRIBUTE_ENTITY_TYPE_ID_EAV_ENTITY_TYPE_ENTITY_TYPE_ID` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COMMENT='Eav Attribute'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
INSERT INTO `eav_attribute` (`attribute_id`, `entity_type_id`, `attribute_code`, `attribute_model`, `backend_model`, `backend_type`, `backend_table`, `frontend_model`, `frontend_input`, `frontend_label`, `frontend_class`, `source_model`, `is_required`, `is_user_defined`, `default_value`, `is_unique`, `note`) VALUES (70, 4, 'sku', NULL, 'Magento\\Catalog\\Model\\Product\\Attribute\\Backend\\Sku', 'static', NULL, NULL, 'text', 'SKU', 'validate-length maximum-length-64', NULL, 1, 0, NULL, 1, NULL);
INSERT INTO `eav_attribute` (`attribute_id`, `entity_type_id`, `attribute_code`, `attribute_model`, `backend_model`, `backend_type`, `backend_table`, `frontend_model`, `frontend_input`, `frontend_label`, `frontend_class`, `source_model`, `is_required`, `is_user_defined`, `default_value`, `is_unique`, `note`) VALUES (1, 1, 'website_id', NULL, 'Magento\\Customer\\Model\\Customer\\Attribute\\Backend\\Website', 'static', NULL, NULL, 'select', 'Associate to Website', NULL, 'Magento\\Customer\\Model\\Customer\\Attribute\\Source\\Website', 1, 0, NULL, 0, NULL);
INSERT INTO `eav_attribute` (`attribute_id`, `entity_type_id`, `attribute_code`, `attribute_model`, `backend_model`, `backend_type`, `backend_table`, `frontend_model`, `frontend_input`, `frontend_label`, `frontend_class`, `source_model`, `is_required`, `is_user_defined`, `default_value`, `is_unique`, `note`) VALUES (115, 4, 'quantity_and_stock_status', NULL, 'Magento\\Catalog\\Model\\Product\\Attribute\\Backend\\Stock', 'int', NULL, NULL, 'select', 'Quantity', NULL, 'Magento\\CatalogInventory\\Model\\Source\\Stock', 0, 0, '1', 0, NULL);

DROP TABLE IF EXISTS `catalog_eav_attribute`;
CREATE TABLE `catalog_eav_attribute` (
	`attribute_id` SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Attribute ID',
	`frontend_input_renderer` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Frontend Input Renderer',
	`is_global` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Is Global',
	`is_visible` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Is Visible',
	`is_searchable` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Is Searchable',
	`is_filterable` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Is Filterable',
	`is_comparable` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Is Comparable',
	`is_visible_on_front` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Is Visible On Front',
	`is_html_allowed_on_front` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Is HTML Allowed On Front',
	`is_used_for_price_rules` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Is Used For Price Rules',
	`is_filterable_in_search` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Is Filterable In Search',
	`used_in_product_listing` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Is Used In Product Listing',
	`used_for_sort_by` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Is Used For Sorting',
	`apply_to` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Apply To',
	`is_visible_in_advanced_search` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Is Visible In Advanced Search',
	`position` INT(11) NOT NULL DEFAULT '0' COMMENT 'Position',
	`is_wysiwyg_enabled` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Is WYSIWYG Enabled',
	`is_used_for_promo_rules` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Is Used For Promo Rules',
	`is_required_in_admin_store` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Is Required In Admin Store',
	`search_weight` FLOAT NOT NULL DEFAULT '3' COMMENT 'Search Weight',
	PRIMARY KEY (`attribute_id`),
	INDEX `IDX_CATALOG_EAV_ATTRIBUTE_USED_FOR_SORT_BY` (`used_for_sort_by`),
	INDEX `IDX_CATALOG_EAV_ATTRIBUTE_USED_IN_PRODUCT_LISTING` (`used_in_product_listing`),
	CONSTRAINT `FK_CATALOG_EAV_ATTRIBUTE_ATTRIBUTE_ID_EAV_ATTRIBUTE_ATTRIBUTE_ID` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COMMENT='Catalog EAV Attribute Table'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
INSERT INTO `catalog_eav_attribute` (`attribute_id`, `frontend_input_renderer`, `is_global`, `is_visible`, `is_searchable`, `is_filterable`, `is_comparable`, `is_visible_on_front`, `is_html_allowed_on_front`, `is_used_for_price_rules`, `is_filterable_in_search`, `used_in_product_listing`, `used_for_sort_by`, `apply_to`, `is_visible_in_advanced_search`, `position`, `is_wysiwyg_enabled`, `is_used_for_promo_rules`, `is_required_in_admin_store`, `search_weight`) VALUES (70, NULL, 1, 1, 1, 0, 1, 0, 0, 0, 0, 0, 0, NULL, 1, 0, 0, 0, 0, 3);

DROP TABLE IF EXISTS `eav_attribute_set`;
CREATE TABLE `eav_attribute_set` (
	`attribute_set_id` SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Attribute Set Id',
	`entity_type_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Entity Type Id',
	`attribute_set_name` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Attribute Set Name',
	`sort_order` SMALLINT(6) NOT NULL DEFAULT '0' COMMENT 'Sort Order',
	PRIMARY KEY (`attribute_set_id`),
	UNIQUE INDEX `UNQ_EAV_ATTRIBUTE_SET_ENTITY_TYPE_ID_ATTRIBUTE_SET_NAME` (`entity_type_id`, `attribute_set_name`),
	INDEX `IDX_EAV_ATTRIBUTE_SET_ENTITY_TYPE_ID_SORT_ORDER` (`entity_type_id`, `sort_order`),
	CONSTRAINT `FK_EAV_ATTR_SET_ENTT_TYPE_ID_EAV_ENTT_TYPE_ENTT_TYPE_ID` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COMMENT='Eav Attribute Set'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
INSERT INTO `eav_attribute_set` (`attribute_set_id`, `entity_type_id`, `attribute_set_name`, `sort_order`) VALUES (4, 4, 'Default', 1);
INSERT INTO `eav_attribute_set` (`attribute_set_id`, `entity_type_id`, `attribute_set_name`, `sort_order`) VALUES (1, 1, 'Default', 2);

DROP TABLE IF EXISTS `eav_attribute_group`;
CREATE TABLE `eav_attribute_group` (
	`attribute_group_id` SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Attribute Group Id',
	`attribute_set_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Attribute Set Id',
	`attribute_group_name` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Attribute Group Name',
	`sort_order` SMALLINT(6) NOT NULL DEFAULT '0' COMMENT 'Sort Order',
	`default_id` SMALLINT(5) UNSIGNED NULL DEFAULT '0' COMMENT 'Default Id',
	`attribute_group_code` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Attribute Group Code',
	`tab_group_code` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Tab Group Code',
	PRIMARY KEY (`attribute_group_id`),
	UNIQUE INDEX `UNQ_EAV_ATTRIBUTE_GROUP_ATTRIBUTE_SET_ID_ATTRIBUTE_GROUP_NAME` (`attribute_set_id`, `attribute_group_name`),
	INDEX `IDX_EAV_ATTRIBUTE_GROUP_ATTRIBUTE_SET_ID_SORT_ORDER` (`attribute_set_id`, `sort_order`),
	CONSTRAINT `FK_EAV_ATTR_GROUP_ATTR_SET_ID_EAV_ATTR_SET_ATTR_SET_ID` FOREIGN KEY (`attribute_set_id`) REFERENCES `eav_attribute_set` (`attribute_set_id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COMMENT='Eav Attribute Group'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
INSERT INTO `eav_attribute_group` (`attribute_group_id`, `attribute_set_id`, `attribute_group_name`, `sort_order`, `default_id`, `attribute_group_code`, `tab_group_code`) VALUES (7, 4, 'Product Details', 10, 1, 'product-details', 'basic');
INSERT INTO `eav_attribute_group` (`attribute_group_id`, `attribute_set_id`, `attribute_group_name`, `sort_order`, `default_id`, `attribute_group_code`, `tab_group_code`) VALUES (1, 1, 'General', 1, 1, NULL, NULL);
INSERT INTO `eav_attribute_group` (`attribute_group_id`, `attribute_set_id`, `attribute_group_name`, `sort_order`, `default_id`, `attribute_group_code`, `tab_group_code`) VALUES (8, 4, 'Design', 11, 1, 'design', 'advanced');

CREATE TABLE `eav_entity_attribute` (
  `entity_attribute_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Entity Attribute Id',
  `entity_type_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Entity Type Id',
  `attribute_set_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Attribute Set Id',
  `attribute_group_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Attribute Group Id',
  `attribute_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Attribute Id',
  `sort_order` SMALLINT(6) NOT NULL DEFAULT '0' COMMENT 'Sort Order',
  PRIMARY KEY (`entity_attribute_id`),
  UNIQUE INDEX `UNQ_EAV_ENTITY_ATTRIBUTE_ATTRIBUTE_SET_ID_ATTRIBUTE_ID` (`attribute_set_id`, `attribute_id`),
  UNIQUE INDEX `UNQ_EAV_ENTITY_ATTRIBUTE_ATTRIBUTE_GROUP_ID_ATTRIBUTE_ID` (`attribute_group_id`, `attribute_id`),
  INDEX `IDX_EAV_ENTITY_ATTRIBUTE_ATTRIBUTE_SET_ID_SORT_ORDER` (`attribute_set_id`, `sort_order`),
  INDEX `IDX_EAV_ENTITY_ATTRIBUTE_ATTRIBUTE_ID` (`attribute_id`),
  CONSTRAINT `FK_EAV_ENTITY_ATTRIBUTE_ATTRIBUTE_ID_EAV_ATTRIBUTE_ATTRIBUTE_ID` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `FK_EAV_ENTT_ATTR_ATTR_GROUP_ID_EAV_ATTR_GROUP_ATTR_GROUP_ID` FOREIGN KEY (`attribute_group_id`) REFERENCES `eav_attribute_group` (`attribute_group_id`) ON UPDATE CASCADE ON DELETE CASCADE
)
  COMMENT='Eav Entity Attributes'
  COLLATE='utf8_general_ci'
  ENGINE=InnoDB
;
INSERT INTO `eav_entity_attribute` (`entity_attribute_id`, `entity_type_id`, `attribute_set_id`, `attribute_group_id`, `attribute_id`, `sort_order`) VALUES (70, 4, 4, 7, 70, 20);
INSERT INTO `eav_entity_attribute` (`entity_attribute_id`, `entity_type_id`, `attribute_set_id`, `attribute_group_id`, `attribute_id`, `sort_order`) VALUES (113, 4, 4, 7, 115, 3);

CREATE TABLE `customer_eav_attribute` (
  `attribute_id` SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Attribute Id',
  `is_visible` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Is Visible',
  `input_filter` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Input Filter',
  `multiline_count` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Multiline Count',
  `validate_rules` TEXT NULL COMMENT 'Validate Rules',
  `is_system` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Is System',
  `sort_order` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Sort Order',
  `data_model` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Data Model',
  `is_used_for_customer_segment` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Customer Segment',
  PRIMARY KEY (`attribute_id`),
  CONSTRAINT `FK_CSTR_EAV_ATTR_ATTR_ID_EAV_ATTR_ATTR_ID` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON UPDATE CASCADE ON DELETE CASCADE
)
  COMMENT='Customer Eav Attribute'
  COLLATE='utf8_general_ci'
  ENGINE=InnoDB
;
INSERT INTO `customer_eav_attribute` (`attribute_id`, `is_visible`, `input_filter`, `multiline_count`, `validate_rules`, `is_system`, `sort_order`, `data_model`, `is_used_for_customer_segment`) VALUES (1, 1, NULL, 0, NULL, 1, 10, NULL, 0);

CREATE TABLE `store_website` (
	`website_id` SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Website Id',
	`code` VARCHAR(32) NULL DEFAULT NULL COMMENT 'Code',
	`name` VARCHAR(64) NULL DEFAULT NULL COMMENT 'Website Name',
	`sort_order` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Sort Order',
	`default_group_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Default Group Id',
	`is_default` SMALLINT(5) UNSIGNED NULL DEFAULT '0' COMMENT 'Defines Is Website Default',
	PRIMARY KEY (`website_id`),
	UNIQUE INDEX `UNQ_STORE_WEBSITE_CODE` (`code`),
	INDEX `IDX_STORE_WEBSITE_SORT_ORDER` (`sort_order`),
	INDEX `IDX_STORE_WEBSITE_DEFAULT_GROUP_ID` (`default_group_id`)
)
COMMENT='Websites'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
INSERT INTO `store_website` (`website_id`, `code`, `name`, `sort_order`, `default_group_id`, `is_default`) VALUES (0, 'admin', 'Admin', 0, 0, 0);
INSERT INTO `store_website` (`website_id`, `code`, `name`, `sort_order`, `default_group_id`, `is_default`) VALUES (1, 'base', 'Main Website', 0, 1, 1);
/*!40101 SET SQL_MODE=IFNULL(@OLD_INSERT_SQL_MODE,'') */;


CREATE TABLE `customer_eav_attribute_website` (
	`attribute_id` SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Attribute Id',
	`website_id` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Website Id',
	`is_visible` SMALLINT(5) UNSIGNED NULL DEFAULT NULL COMMENT 'Is Visible',
	`is_required` SMALLINT(5) UNSIGNED NULL DEFAULT NULL COMMENT 'Is Required',
	`default_value` TEXT NULL COMMENT 'Default Value',
	`multiline_count` SMALLINT(5) UNSIGNED NULL DEFAULT NULL COMMENT 'Multiline Count',
	PRIMARY KEY (`attribute_id`, `website_id`),
	INDEX `IDX_CUSTOMER_EAV_ATTRIBUTE_WEBSITE_WEBSITE_ID` (`website_id`),
	CONSTRAINT `FK_CSTR_EAV_ATTR_WS_ATTR_ID_EAV_ATTR_ATTR_ID` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK_CSTR_EAV_ATTR_WS_WS_ID_STORE_WS_WS_ID` FOREIGN KEY (`website_id`) REFERENCES `store_website` (`website_id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COMMENT='Customer Eav Attribute Website'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;

CREATE TABLE `store_group` (
	`group_id` SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Group Id',
	`website_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Website Id',
	`name` VARCHAR(255) NOT NULL COMMENT 'Store Group Name',
	`root_category_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Root Category Id',
	`default_store_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Default Store Id',
	PRIMARY KEY (`group_id`),
	INDEX `IDX_STORE_GROUP_WEBSITE_ID` (`website_id`),
	INDEX `IDX_STORE_GROUP_DEFAULT_STORE_ID` (`default_store_id`),
	CONSTRAINT `FK_STORE_GROUP_WEBSITE_ID_STORE_WEBSITE_WEBSITE_ID` FOREIGN KEY (`website_id`) REFERENCES `store_website` (`website_id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COMMENT='Store Groups'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
INSERT INTO `store_group` (`group_id`, `website_id`, `name`, `root_category_id`, `default_store_id`) VALUES (0, 0, 'Default', 0, 0);
INSERT INTO `store_group` (`group_id`, `website_id`, `name`, `root_category_id`, `default_store_id`) VALUES (1, 1, 'Main Website Store', 2, 1);
/*!40101 SET SQL_MODE=IFNULL(@OLD_INSERT_SQL_MODE,'') */;

CREATE TABLE `store` (
	`store_id` SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Store Id',
	`code` VARCHAR(32) NULL DEFAULT NULL COMMENT 'Code',
	`website_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Website Id',
	`group_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Group Id',
	`name` VARCHAR(255) NOT NULL COMMENT 'Store Name',
	`sort_order` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Store Sort Order',
	`is_active` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Store Activity',
	PRIMARY KEY (`store_id`),
	UNIQUE INDEX `UNQ_STORE_CODE` (`code`),
	INDEX `IDX_STORE_WEBSITE_ID` (`website_id`),
	INDEX `IDX_STORE_IS_ACTIVE_SORT_ORDER` (`is_active`, `sort_order`),
	INDEX `IDX_STORE_GROUP_ID` (`group_id`),
	CONSTRAINT `FK_STORE_GROUP_ID_STORE_GROUP_GROUP_ID` FOREIGN KEY (`group_id`) REFERENCES `store_group` (`group_id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK_STORE_WEBSITE_ID_STORE_WEBSITE_WEBSITE_ID` FOREIGN KEY (`website_id`) REFERENCES `store_website` (`website_id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COMMENT='Stores'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
INSERT INTO `store` (`store_id`, `code`, `website_id`, `group_id`, `name`, `sort_order`, `is_active`) VALUES (0, 'admin', 0, 0, 'Admin', 0, 1);
INSERT INTO `store` (`store_id`, `code`, `website_id`, `group_id`, `name`, `sort_order`, `is_active`) VALUES (1, 'default', 1, 1, 'Default Store View', 0, 1);
/*!40101 SET SQL_MODE=IFNULL(@OLD_INSERT_SQL_MODE,'') */;

CREATE TABLE `eav_attribute_label` (
  `attribute_label_id` INT(10) UNSIGNED NOT NULL  COMMENT 'Attribute Label Id',
  `attribute_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Attribute Id',
  `store_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Store Id',
  `value` VARCHAR(255) NOT NULL COMMENT 'Value',
  PRIMARY KEY (`attribute_label_id`),
  INDEX `IDX_EAV_ATTRIBUTE_LABEL_ATTRIBUTE_ID` (`attribute_id`),
  INDEX `IDX_EAV_ATTRIBUTE_LABEL_STORE_ID` (`store_id`),
  INDEX `IDX_EAV_ATTRIBUTE_LABEL_ATTRIBUTE_ID_STORE_ID` (`attribute_id`, `store_id`),
  CONSTRAINT `FK_EAV_ATTRIBUTE_LABEL_ATTRIBUTE_ID_EAV_ATTRIBUTE_ATTRIBUTE_ID` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `FK_EAV_ATTRIBUTE_LABEL_STORE_ID_CORE_STORE_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `store` (`store_id`) ON UPDATE CASCADE ON DELETE CASCADE
)
  COMMENT='Eav Attribute Label'
  COLLATE='utf8_general_ci'
  ENGINE=InnoDB
;

CREATE TABLE `eav_attribute_option` (
  `option_id` INT(10) UNSIGNED NOT NULL COMMENT 'Option Id',
  `attribute_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Attribute Id',
  `sort_order` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Sort Order',
  PRIMARY KEY (`option_id`),
  INDEX `IDX_EAV_ATTRIBUTE_OPTION_ATTRIBUTE_ID` (`attribute_id`),
  CONSTRAINT `FK_EAV_ATTRIBUTE_OPTION_ATTRIBUTE_ID_EAV_ATTRIBUTE_ATTRIBUTE_ID` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON UPDATE CASCADE ON DELETE CASCADE
)
  COMMENT='Eav Attribute Option'
  COLLATE='utf8_general_ci'
  ENGINE=InnoDB
;

CREATE TABLE `eav_attribute_option_value` (
  `value_id` INT(10) UNSIGNED NOT NULL COMMENT 'Value Id',
  `option_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Option Id',
  `store_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Store Id',
  `value` VARCHAR(255) NOT NULL COMMENT 'Value',
  PRIMARY KEY (`value_id`),
  INDEX `IDX_EAV_ATTRIBUTE_OPTION_VALUE_OPTION_ID` (`option_id`),
  INDEX `IDX_EAV_ATTRIBUTE_OPTION_VALUE_STORE_ID` (`store_id`),
  CONSTRAINT `FK_EAV_ATTRIBUTE_OPTION_VALUE_STORE_ID_CORE_STORE_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `store` (`store_id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `FK_EAV_ATTR_OPT_VAL_OPT_ID_EAV_ATTR_OPT_OPT_ID` FOREIGN KEY (`option_id`) REFERENCES `eav_attribute_option` (`option_id`) ON UPDATE CASCADE ON DELETE CASCADE
)
  COMMENT='Eav Attribute Option Value'
  COLLATE='utf8_general_ci'
  ENGINE=InnoDB
;

CREATE TABLE `eav_entity` (
  `entity_id` INT(10) UNSIGNED NOT NULL COMMENT 'Entity Id',
  `entity_type_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Entity Type Id',
  `attribute_set_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Attribute Set Id',
  `increment_id` VARCHAR(50) NOT NULL COMMENT 'Increment Id',
  `parent_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Parent Id',
  `store_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Store Id',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Created At',
  `updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Updated At',
  `is_active` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Defines Is Entity Active',
  PRIMARY KEY (`entity_id`),
  INDEX `IDX_EAV_ENTITY_ENTITY_TYPE_ID` (`entity_type_id`),
  INDEX `IDX_EAV_ENTITY_STORE_ID` (`store_id`),
  CONSTRAINT `FK_EAV_ENTITY_ENTITY_TYPE_ID_EAV_ENTITY_TYPE_ENTITY_TYPE_ID` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `FK_EAV_ENTITY_STORE_ID_CORE_STORE_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `store` (`store_id`) ON UPDATE CASCADE ON DELETE CASCADE
)
  COMMENT='Eav Entity'
  COLLATE='utf8_general_ci'
  ENGINE=InnoDB
;

CREATE TABLE `eav_entity_datetime` (
  `value_id` INT(11) NOT NULL COMMENT 'Value Id',
  `entity_type_id` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Entity Type Id',
  `attribute_id` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Attribute Id',
  `store_id` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Store Id',
  `entity_id` INT(10) UNSIGNED NOT NULL COMMENT 'Entity Id',
  `value` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Attribute Value',
  PRIMARY KEY (`value_id`),
  UNIQUE INDEX `UNQ_EAV_ENTITY_DATETIME_ENTITY_ID_ATTRIBUTE_ID_STORE_ID` (`entity_id`, `attribute_id`, `store_id`),
  INDEX `IDX_EAV_ENTITY_DATETIME_ENTITY_TYPE_ID` (`entity_type_id`),
  INDEX `IDX_EAV_ENTITY_DATETIME_ATTRIBUTE_ID` (`attribute_id`),
  INDEX `IDX_EAV_ENTITY_DATETIME_STORE_ID` (`store_id`),
  INDEX `IDX_EAV_ENTITY_DATETIME_ENTITY_ID` (`entity_id`),
  INDEX `IDX_EAV_ENTITY_DATETIME_ATTRIBUTE_ID_VALUE` (`attribute_id`, `value`),
  INDEX `IDX_EAV_ENTITY_DATETIME_ENTITY_TYPE_ID_VALUE` (`entity_type_id`, `value`),
  CONSTRAINT `FK_EAV_ENTITY_DATETIME_ENTITY_ID_EAV_ENTITY_ENTITY_ID` FOREIGN KEY (`entity_id`) REFERENCES `eav_entity` (`entity_id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `FK_EAV_ENTITY_DATETIME_STORE_ID_CORE_STORE_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `store` (`store_id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `FK_EAV_ENTT_DTIME_ENTT_TYPE_ID_EAV_ENTT_TYPE_ENTT_TYPE_ID` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON UPDATE CASCADE ON DELETE CASCADE
)
  COMMENT='Eav Entity Value Prefix'
  COLLATE='utf8_general_ci'
  ENGINE=InnoDB
;

CREATE TABLE `eav_entity_decimal` (
  `value_id` INT(11) NOT NULL COMMENT 'Value Id',
  `entity_type_id` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Entity Type Id',
  `attribute_id` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Attribute Id',
  `store_id` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Store Id',
  `entity_id` INT(10) UNSIGNED NOT NULL COMMENT 'Entity Id',
  `value` DECIMAL(12,4) NOT NULL DEFAULT '0.0000' COMMENT 'Attribute Value',
  PRIMARY KEY (`value_id`),
  UNIQUE INDEX `UNQ_EAV_ENTITY_DECIMAL_ENTITY_ID_ATTRIBUTE_ID_STORE_ID` (`entity_id`, `attribute_id`, `store_id`),
  INDEX `IDX_EAV_ENTITY_DECIMAL_ENTITY_TYPE_ID` (`entity_type_id`),
  INDEX `IDX_EAV_ENTITY_DECIMAL_ATTRIBUTE_ID` (`attribute_id`),
  INDEX `IDX_EAV_ENTITY_DECIMAL_STORE_ID` (`store_id`),
  INDEX `IDX_EAV_ENTITY_DECIMAL_ENTITY_ID` (`entity_id`),
  INDEX `IDX_EAV_ENTITY_DECIMAL_ATTRIBUTE_ID_VALUE` (`attribute_id`, `value`),
  INDEX `IDX_EAV_ENTITY_DECIMAL_ENTITY_TYPE_ID_VALUE` (`entity_type_id`, `value`),
  CONSTRAINT `FK_EAV_ENTITY_DECIMAL_ENTITY_ID_EAV_ENTITY_ENTITY_ID` FOREIGN KEY (`entity_id`) REFERENCES `eav_entity` (`entity_id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `FK_EAV_ENTITY_DECIMAL_STORE_ID_CORE_STORE_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `store` (`store_id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `FK_EAV_ENTT_DEC_ENTT_TYPE_ID_EAV_ENTT_TYPE_ENTT_TYPE_ID` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON UPDATE CASCADE ON DELETE CASCADE
)
  COMMENT='Eav Entity Value Prefix'
  COLLATE='utf8_general_ci'
  ENGINE=InnoDB
;

CREATE TABLE `eav_entity_int` (
  `value_id` INT(11) NOT NULL COMMENT 'Value Id',
  `entity_type_id` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Entity Type Id',
  `attribute_id` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Attribute Id',
  `store_id` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Store Id',
  `entity_id` INT(10) UNSIGNED NOT NULL COMMENT 'Entity Id',
  `value` INT(11) NOT NULL COMMENT 'Attribute Value',
  PRIMARY KEY (`value_id`),
  UNIQUE INDEX `UNQ_EAV_ENTITY_INT_ENTITY_ID_ATTRIBUTE_ID_STORE_ID` (`entity_id`, `attribute_id`, `store_id`),
  INDEX `IDX_EAV_ENTITY_INT_ENTITY_TYPE_ID` (`entity_type_id`),
  INDEX `IDX_EAV_ENTITY_INT_ATTRIBUTE_ID` (`attribute_id`),
  INDEX `IDX_EAV_ENTITY_INT_STORE_ID` (`store_id`),
  INDEX `IDX_EAV_ENTITY_INT_ENTITY_ID` (`entity_id`),
  INDEX `IDX_EAV_ENTITY_INT_ATTRIBUTE_ID_VALUE` (`attribute_id`, `value`),
  INDEX `IDX_EAV_ENTITY_INT_ENTITY_TYPE_ID_VALUE` (`entity_type_id`, `value`),
  CONSTRAINT `FK_EAV_ENTITY_INT_ENTITY_ID_EAV_ENTITY_ENTITY_ID` FOREIGN KEY (`entity_id`) REFERENCES `eav_entity` (`entity_id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `FK_EAV_ENTITY_INT_ENTITY_TYPE_ID_EAV_ENTITY_TYPE_ENTITY_TYPE_ID` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `FK_EAV_ENTITY_INT_STORE_ID_CORE_STORE_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `store` (`store_id`) ON UPDATE CASCADE ON DELETE CASCADE
)
  COMMENT='Eav Entity Value Prefix'
  COLLATE='utf8_general_ci'
  ENGINE=InnoDB
;

CREATE TABLE `eav_entity_store` (
  `entity_store_id` INT(10) UNSIGNED NOT NULL COMMENT 'Entity Store Id',
  `entity_type_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Entity Type Id',
  `store_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Store Id',
  `increment_prefix` VARCHAR(20) NULL DEFAULT NULL COMMENT 'Increment Prefix',
  `increment_last_id` VARCHAR(50) NULL DEFAULT NULL COMMENT 'Last Incremented Id',
  PRIMARY KEY (`entity_store_id`),
  INDEX `IDX_EAV_ENTITY_STORE_ENTITY_TYPE_ID` (`entity_type_id`),
  INDEX `IDX_EAV_ENTITY_STORE_STORE_ID` (`store_id`),
  CONSTRAINT `FK_EAV_ENTITY_STORE_STORE_ID_CORE_STORE_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `store` (`store_id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `FK_EAV_ENTT_STORE_ENTT_TYPE_ID_EAV_ENTT_TYPE_ENTT_TYPE_ID` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON UPDATE CASCADE ON DELETE CASCADE
)
  COMMENT='Eav Entity Store'
  COLLATE='utf8_general_ci'
  ENGINE=InnoDB
;

CREATE TABLE `eav_entity_text` (
  `value_id` INT(11) NOT NULL COMMENT 'Value Id',
  `entity_type_id` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Entity Type Id',
  `attribute_id` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Attribute Id',
  `store_id` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Store Id',
  `entity_id` INT(10) UNSIGNED NOT NULL COMMENT 'Entity Id',
  `value` TEXT NOT NULL COMMENT 'Attribute Value',
  PRIMARY KEY (`value_id`),
  UNIQUE INDEX `UNQ_EAV_ENTITY_TEXT_ENTITY_ID_ATTRIBUTE_ID_STORE_ID` (`entity_id`, `attribute_id`, `store_id`),
  INDEX `IDX_EAV_ENTITY_TEXT_ENTITY_TYPE_ID` (`entity_type_id`),
  INDEX `IDX_EAV_ENTITY_TEXT_ATTRIBUTE_ID` (`attribute_id`),
  INDEX `IDX_EAV_ENTITY_TEXT_STORE_ID` (`store_id`),
  INDEX `IDX_EAV_ENTITY_TEXT_ENTITY_ID` (`entity_id`),
  CONSTRAINT `FK_EAV_ENTITY_TEXT_ENTITY_ID_EAV_ENTITY_ENTITY_ID` FOREIGN KEY (`entity_id`) REFERENCES `eav_entity` (`entity_id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `FK_EAV_ENTITY_TEXT_ENTITY_TYPE_ID_EAV_ENTITY_TYPE_ENTITY_TYPE_ID` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `FK_EAV_ENTITY_TEXT_STORE_ID_CORE_STORE_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `store` (`store_id`) ON UPDATE CASCADE ON DELETE CASCADE
)
  COMMENT='Eav Entity Value Prefix'
  COLLATE='utf8_general_ci'
  ENGINE=InnoDB
;

CREATE TABLE `eav_entity_varchar` (
  `value_id` INT(11) NOT NULL COMMENT 'Value Id',
  `entity_type_id` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Entity Type Id',
  `attribute_id` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Attribute Id',
  `store_id` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Store Id',
  `entity_id` INT(10) UNSIGNED NOT NULL COMMENT 'Entity Id',
  `value` VARCHAR(255) NOT NULL COMMENT 'Attribute Value',
  PRIMARY KEY (`value_id`),
  UNIQUE INDEX `UNQ_EAV_ENTITY_VARCHAR_ENTITY_ID_ATTRIBUTE_ID_STORE_ID` (`entity_id`, `attribute_id`, `store_id`),
  INDEX `IDX_EAV_ENTITY_VARCHAR_ENTITY_TYPE_ID` (`entity_type_id`),
  INDEX `IDX_EAV_ENTITY_VARCHAR_ATTRIBUTE_ID` (`attribute_id`),
  INDEX `IDX_EAV_ENTITY_VARCHAR_STORE_ID` (`store_id`),
  INDEX `IDX_EAV_ENTITY_VARCHAR_ENTITY_ID` (`entity_id`),
  INDEX `IDX_EAV_ENTITY_VARCHAR_ATTRIBUTE_ID_VALUE` (`attribute_id`, `value`),
  INDEX `IDX_EAV_ENTITY_VARCHAR_ENTITY_TYPE_ID_VALUE` (`entity_type_id`, `value`),
  CONSTRAINT `FK_EAV_ENTITY_VARCHAR_ENTITY_ID_EAV_ENTITY_ENTITY_ID` FOREIGN KEY (`entity_id`) REFERENCES `eav_entity` (`entity_id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `FK_EAV_ENTITY_VARCHAR_STORE_ID_CORE_STORE_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `store` (`store_id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `FK_EAV_ENTT_VCHR_ENTT_TYPE_ID_EAV_ENTT_TYPE_ENTT_TYPE_ID` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON UPDATE CASCADE ON DELETE CASCADE
)
  COMMENT='Eav Entity Value Prefix'
  COLLATE='utf8_general_ci'
  ENGINE=InnoDB
;

CREATE TABLE `eav_form_type` (
  `type_id` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Type Id',
  `code` VARCHAR(64) NOT NULL COMMENT 'Code',
  `label` VARCHAR(255) NOT NULL COMMENT 'Label',
  `is_system` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Is System',
  `theme` VARCHAR(64) NULL DEFAULT NULL COMMENT 'Theme',
  `store_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Store Id',
  PRIMARY KEY (`type_id`),
  UNIQUE INDEX `UNQ_EAV_FORM_TYPE_CODE_THEME_STORE_ID` (`code`, `theme`, `store_id`),
  INDEX `IDX_EAV_FORM_TYPE_STORE_ID` (`store_id`),
  CONSTRAINT `FK_EAV_FORM_TYPE_STORE_ID_CORE_STORE_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `store` (`store_id`) ON UPDATE CASCADE ON DELETE CASCADE
)
  COMMENT='Eav Form Type'
  COLLATE='utf8_general_ci'
  ENGINE=InnoDB
;

CREATE TABLE `eav_form_fieldset` (
  `fieldset_id` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Fieldset Id',
  `type_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Type Id',
  `code` VARCHAR(64) NOT NULL COMMENT 'Code',
  `sort_order` INT(11) NOT NULL DEFAULT '0' COMMENT 'Sort Order',
  PRIMARY KEY (`fieldset_id`),
  UNIQUE INDEX `UNQ_EAV_FORM_FIELDSET_TYPE_ID_CODE` (`type_id`, `code`),
  INDEX `IDX_EAV_FORM_FIELDSET_TYPE_ID` (`type_id`),
  CONSTRAINT `FK_EAV_FORM_FIELDSET_TYPE_ID_EAV_FORM_TYPE_TYPE_ID` FOREIGN KEY (`type_id`) REFERENCES `eav_form_type` (`type_id`) ON UPDATE CASCADE ON DELETE CASCADE
)
  COMMENT='Eav Form Fieldset'
  COLLATE='utf8_general_ci'
  ENGINE=InnoDB
;

CREATE TABLE `eav_form_element` (
  `element_id` INT(10) UNSIGNED NOT NULL COMMENT 'Element Id',
  `type_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Type Id',
  `fieldset_id` SMALLINT(5) UNSIGNED NULL DEFAULT NULL COMMENT 'Fieldset Id',
  `attribute_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Attribute Id',
  `sort_order` INT(11) NOT NULL DEFAULT '0' COMMENT 'Sort Order',
  PRIMARY KEY (`element_id`),
  UNIQUE INDEX `UNQ_EAV_FORM_ELEMENT_TYPE_ID_ATTRIBUTE_ID` (`type_id`, `attribute_id`),
  INDEX `IDX_EAV_FORM_ELEMENT_TYPE_ID` (`type_id`),
  INDEX `IDX_EAV_FORM_ELEMENT_FIELDSET_ID` (`fieldset_id`),
  INDEX `IDX_EAV_FORM_ELEMENT_ATTRIBUTE_ID` (`attribute_id`),
  CONSTRAINT `FK_EAV_FORM_ELEMENT_ATTRIBUTE_ID_EAV_ATTRIBUTE_ATTRIBUTE_ID` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `FK_EAV_FORM_ELEMENT_FIELDSET_ID_EAV_FORM_FIELDSET_FIELDSET_ID` FOREIGN KEY (`fieldset_id`) REFERENCES `eav_form_fieldset` (`fieldset_id`) ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `FK_EAV_FORM_ELEMENT_TYPE_ID_EAV_FORM_TYPE_TYPE_ID` FOREIGN KEY (`type_id`) REFERENCES `eav_form_type` (`type_id`) ON UPDATE CASCADE ON DELETE CASCADE
)
  COMMENT='Eav Form Element'
  COLLATE='utf8_general_ci'
  ENGINE=InnoDB
;

CREATE TABLE `eav_form_fieldset_label` (
  `fieldset_id` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Fieldset Id',
  `store_id` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Store Id',
  `label` VARCHAR(255) NOT NULL COMMENT 'Label',
  PRIMARY KEY (`fieldset_id`, `store_id`),
  INDEX `IDX_EAV_FORM_FIELDSET_LABEL_STORE_ID` (`store_id`),
  CONSTRAINT `FK_EAV_FORM_FIELDSET_LABEL_STORE_ID_STORE_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `store` (`store_id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `FK_EAV_FORM_FSET_LBL_FSET_ID_EAV_FORM_FSET_FSET_ID` FOREIGN KEY (`fieldset_id`) REFERENCES `eav_form_fieldset` (`fieldset_id`) ON UPDATE CASCADE ON DELETE CASCADE
)
  COMMENT='Eav Form Fieldset Label'
  COLLATE='utf8_general_ci'
  ENGINE=InnoDB
;

CREATE TABLE `eav_form_type_entity` (
  `type_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Type Id',
  `entity_type_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Entity Type Id',
  PRIMARY KEY (`type_id`, `entity_type_id`),
  INDEX `IDX_EAV_FORM_TYPE_ENTITY_ENTITY_TYPE_ID` (`entity_type_id`),
  CONSTRAINT `FK_EAV_FORM_TYPE_ENTITY_TYPE_ID_EAV_FORM_TYPE_TYPE_ID` FOREIGN KEY (`type_id`) REFERENCES `eav_form_type` (`type_id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `FK_EAV_FORM_TYPE_ENTT_ENTT_TYPE_ID_EAV_ENTT_TYPE_ENTT_TYPE_ID` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON UPDATE CASCADE ON DELETE CASCADE
)
  COMMENT='Eav Form Type Entity'
  COLLATE='utf8_general_ci'
  ENGINE=InnoDB
;
