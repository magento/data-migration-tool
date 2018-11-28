/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;

CREATE TABLE `eav_entity_type` (
	`entity_type_id` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Entity Type Id',
	`entity_type_code` VARCHAR(50) NOT NULL COMMENT 'Entity Type Code',
	`entity_model` VARCHAR(255) NOT NULL COMMENT 'Entity Model',
	`attribute_model` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Attribute Model',
	`entity_table` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Entity Table',
	`value_table_prefix` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Value Table Prefix',
	`entity_id_field` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Entity Id Field',
	`is_data_sharing` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Defines Is Data Sharing',
	`data_sharing_key` VARCHAR(100) NULL DEFAULT 'default' COMMENT 'Data Sharing Key',
	`default_attribute_set_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Default Attribute Set Id',
	`increment_model` VARCHAR(255) NULL DEFAULT '' COMMENT 'Increment Model',
	`increment_per_store` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Increment Per Store',
	`increment_pad_length` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '8' COMMENT 'Increment Pad Length',
	`increment_pad_char` VARCHAR(1) NOT NULL DEFAULT '0' COMMENT 'Increment Pad Char',
	`additional_attribute_table` VARCHAR(255) NULL DEFAULT '' COMMENT 'Additional Attribute Table',
	`entity_attribute_collection` VARCHAR(255) NULL DEFAULT '' COMMENT 'Entity Attribute Collection',
	PRIMARY KEY (`entity_type_id`),
	INDEX `IDX_EAV_ENTITY_TYPE_ENTITY_TYPE_CODE` (`entity_type_code`)
) ENGINE=InnoDB COMMENT='Eav Entity Type' COLLATE='utf8_general_ci';

INSERT INTO `eav_entity_type` (`entity_type_id`, `entity_type_code`, `entity_model`, `attribute_model`, `entity_table`, `value_table_prefix`, `entity_id_field`, `is_data_sharing`, `data_sharing_key`, `default_attribute_set_id`, `increment_model`, `increment_per_store`, `increment_pad_length`, `increment_pad_char`, `additional_attribute_table`, `entity_attribute_collection`) VALUES (4, 'catalog_product', 'catalog/product', 'catalog/resource_eav_attribute', 'catalog/product', '', '', 1, 'default', 4, '', 0, 8, '0', 'catalog/eav_attribute', 'catalog/product_attribute_collection');
INSERT INTO `eav_entity_type` (`entity_type_id`, `entity_type_code`, `entity_model`, `attribute_model`, `entity_table`, `value_table_prefix`, `entity_id_field`, `is_data_sharing`, `data_sharing_key`, `default_attribute_set_id`, `increment_model`, `increment_per_store`, `increment_pad_length`, `increment_pad_char`, `additional_attribute_table`, `entity_attribute_collection`) VALUES (1, 'customer', 'customer/customer', 'customer/attribute', 'customer/entity', '', '', 1, 'default', 1, 'eav/entity_increment_numeric', 0, 8, '0', 'customer/eav_attribute', 'customer/attribute_collection');

CREATE TABLE `eav_attribute` (
	`attribute_id` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Attribute Id',
	`entity_type_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Entity Type Id',
	`attribute_code` VARCHAR(255) NOT NULL COMMENT 'Attribute Code',
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
	INDEX `IDX_EAV_ATTRIBUTE_ENTITY_TYPE_ID` (`entity_type_id`),
	CONSTRAINT `FK_EAV_ATTRIBUTE_ENTITY_TYPE_ID_EAV_ENTITY_TYPE_ENTITY_TYPE_ID` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB COMMENT='Eav Attribute' COLLATE='utf8_general_ci';

INSERT INTO `eav_attribute` (`attribute_id`, `entity_type_id`, `attribute_code`, `attribute_model`, `backend_model`, `backend_type`, `backend_table`, `frontend_model`, `frontend_input`, `frontend_label`, `frontend_class`, `source_model`, `is_required`, `is_user_defined`, `default_value`, `is_unique`, `note`) VALUES (63, 4, 'sku', NULL, 'catalog/product_attribute_backend_sku', 'static', '', '', 'text', 'SKU', '', '', 1, 0, '', 1, '');
INSERT INTO `eav_attribute` (`attribute_id`, `entity_type_id`, `attribute_code`, `attribute_model`, `backend_model`, `backend_type`, `backend_table`, `frontend_model`, `frontend_input`, `frontend_label`, `frontend_class`, `source_model`, `is_required`, `is_user_defined`, `default_value`, `is_unique`, `note`) VALUES (1, 1, 'website_id', NULL, 'customer/customer_attribute_backend_website', 'static', NULL, NULL, 'select', 'Associate to Website', NULL, 'customer/customer_attribute_source_website', 1, 0, NULL, 0, NULL);
INSERT INTO `eav_attribute` (`attribute_id`, `entity_type_id`, `attribute_code`, `attribute_model`, `backend_model`, `backend_type`, `backend_table`, `frontend_model`, `frontend_input`, `frontend_label`, `frontend_class`, `source_model`, `is_required`, `is_user_defined`, `default_value`, `is_unique`, `note`) VALUES (118, 4, 'msrp_enabled', NULL, 'catalog/product_attribute_backend_msrp', 'varchar', NULL, NULL, 'select', 'Apply MAP', NULL, 'catalog/product_attribute_source_msrp_type_enabled', 0, 0, '2', 0, NULL);

CREATE TABLE `eav_attribute_set` (
  `attribute_set_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Attribute Set Id',
  `entity_type_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Entity Type Id',
  `attribute_set_name` varchar(255) NOT NULL COMMENT 'Attribute Set Name',
  `sort_order` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Sort Order',
  PRIMARY KEY (`attribute_set_id`),
  UNIQUE KEY `UNQ_EAV_ATTRIBUTE_SET_ENTITY_TYPE_ID_ATTRIBUTE_SET_NAME` (`entity_type_id`,`attribute_set_name`),
  KEY `IDX_EAV_ATTRIBUTE_SET_ENTITY_TYPE_ID_SORT_ORDER` (`entity_type_id`,`sort_order`),
  CONSTRAINT `FK_EAV_ATTR_SET_ENTT_TYPE_ID_EAV_ENTT_TYPE_ENTT_TYPE_ID` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='Eav Attribute Set';

INSERT INTO `eav_attribute_set` (`entity_type_id`, `attribute_set_name`, `sort_order`) VALUES (1, 'Default', 1);
INSERT INTO `eav_attribute_set` (`entity_type_id`, `attribute_set_name`, `sort_order`) VALUES (2, 'Default', 1);
INSERT INTO `eav_attribute_set` (`entity_type_id`, `attribute_set_name`, `sort_order`) VALUES (3, 'Default', 1);
INSERT INTO `eav_attribute_set` (`entity_type_id`, `attribute_set_name`, `sort_order`) VALUES (4, 'Default', 2);

SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
