DROP TABLE IF EXISTS `eav_entity_type`;
CREATE TABLE IF NOT EXISTS `eav_entity_type` (
  `entity_type_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Entity Type Id',
  `entity_type_code` varchar(50) NOT NULL COMMENT 'Entity Type Code',
  `entity_model` varchar(255) NOT NULL COMMENT 'Entity Model',
  `attribute_model` varchar(255) DEFAULT NULL COMMENT 'Attribute Model',
  `entity_table` varchar(255) DEFAULT NULL COMMENT 'Entity Table',
  `value_table_prefix` varchar(255) DEFAULT NULL COMMENT 'Value Table Prefix',
  `entity_id_field` varchar(255) DEFAULT NULL COMMENT 'Entity Id Field',
  `is_data_sharing` smallint(5) unsigned NOT NULL DEFAULT '1' COMMENT 'Defines Is Data Sharing',
  `data_sharing_key` varchar(100) DEFAULT 'default' COMMENT 'Data Sharing Key',
  `default_attribute_set_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Default Attribute Set Id',
  `increment_model` varchar(255) DEFAULT NULL COMMENT 'Increment Model',
  `increment_per_store` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Increment Per Store',
  `increment_pad_length` smallint(5) unsigned NOT NULL DEFAULT '8' COMMENT 'Increment Pad Length',
  `increment_pad_char` varchar(1) NOT NULL DEFAULT '0' COMMENT 'Increment Pad Char',
  `additional_attribute_table` varchar(255) DEFAULT NULL COMMENT 'Additional Attribute Table',
  `entity_attribute_collection` varchar(255) DEFAULT NULL COMMENT 'Entity Attribute Collection',
  PRIMARY KEY (`entity_type_id`),
  KEY `EAV_ENTITY_TYPE_ENTITY_TYPE_CODE` (`entity_type_code`)
)
COMMENT='Eav Entity Type'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;

INSERT INTO `eav_entity_type` (`entity_type_id`, `entity_type_code`, `entity_model`, `attribute_model`, `entity_table`, `value_table_prefix`, `entity_id_field`, `is_data_sharing`, `data_sharing_key`, `default_attribute_set_id`, `increment_model`, `increment_per_store`, `increment_pad_length`, `increment_pad_char`, `additional_attribute_table`, `entity_attribute_collection`)
VALUES (4, 'catalog_product', 'catalog/product', 'catalog/resource_eav_attribute', 'catalog/product', '', '', 1, 'default', 4, '', 0, 8, '0', 'catalog/eav_attribute', 'catalog/product_attribute_collection'),
       (1, 'customer', 'customer/customer', 'customer/attribute', 'customer/entity', '', '', 1, 'default', 1, 'eav/entity_increment_numeric', 0, 8, '0', 'customer/eav_attribute', 'customer/attribute_collection');

DROP TABLE IF EXISTS `eav_attribute_set`;
CREATE TABLE IF NOT EXISTS `eav_attribute_set` (
  `attribute_set_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Attribute Set Id',
  `entity_type_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Entity Type Id',
  `attribute_set_name` varchar(255) DEFAULT NULL COMMENT 'Attribute Set Name',
  `sort_order` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Sort Order',
  PRIMARY KEY (`attribute_set_id`),
  UNIQUE KEY `EAV_ATTRIBUTE_SET_ENTITY_TYPE_ID_ATTRIBUTE_SET_NAME` (`entity_type_id`,`attribute_set_name`),
  KEY `EAV_ATTRIBUTE_SET_ENTITY_TYPE_ID_SORT_ORDER` (`entity_type_id`,`sort_order`),
  CONSTRAINT `EAV_ATTRIBUTE_SET_ENTITY_TYPE_ID_EAV_ENTITY_TYPE_ENTITY_TYPE_ID` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON DELETE CASCADE
)
COMMENT='Eav Attribute Set'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
INSERT INTO `eav_attribute_set` (`attribute_set_id`, `entity_type_id`, `attribute_set_name`, `sort_order`)
  VALUES (4, 4, 'Default', 1), (1, 1, 'Default', 1);

DROP TABLE IF EXISTS `catalog_category_entity`;
CREATE TABLE `catalog_category_entity` (
	`entity_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Entity ID',
	`entity_type_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Entity Type ID',
	`attribute_set_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Attriute Set ID',
	`parent_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Parent Category ID',
	`created_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Creation Time',
	`updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Update Time',
	`path` VARCHAR(255) NOT NULL COMMENT 'Tree Path',
	`position` INT(11) NOT NULL DEFAULT '0' COMMENT 'Position',
	`level` INT(11) NOT NULL DEFAULT '0' COMMENT 'Tree Level',
	`children_count` INT(11) NOT NULL DEFAULT '0' COMMENT 'Child Count',
	PRIMARY KEY (`entity_id`),
	INDEX `IDX_CATALOG_CATEGORY_ENTITY_LEVEL` (`level`),
	INDEX `IDX_CATALOG_CATEGORY_ENTITY_PATH_ENTITY_ID` (`path`, `entity_id`)
)
COMMENT='Catalog Category Table'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
INSERT INTO `catalog_category_entity` VALUES (1, 3, 0, 0, '1975-01-01 00:00:00', '2016-07-13 11:43:28', '1', 0, 0, 2),
(2, 3, 3, 1, '2016-07-13 11:43:28', '2016-07-13 11:43:28', '1/2', 1, 1, 1),
(3, 3, 3, 2, '2016-07-13 12:30:21', '2016-07-13 12:30:21', '1/2/3', 1, 2, 0);

DROP TABLE IF EXISTS `catalog_product_entity`;
CREATE TABLE `catalog_product_entity` (
	`entity_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Entity ID',
	`entity_type_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Entity Type ID',
	`attribute_set_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Attribute Set ID',
	`type_id` VARCHAR(32) NOT NULL DEFAULT 'simple' COMMENT 'Type ID',
	`sku` VARCHAR(64) NULL DEFAULT NULL COMMENT 'SKU',
	`has_options` SMALLINT(6) NOT NULL DEFAULT '0' COMMENT 'Has Options',
	`required_options` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Required Options',
	`created_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Creation Time',
	`updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Update Time',
	PRIMARY KEY (`entity_id`),
	INDEX `IDX_CATALOG_PRODUCT_ENTITY_ENTITY_TYPE_ID` (`entity_type_id`),
	INDEX `IDX_CATALOG_PRODUCT_ENTITY_ATTRIBUTE_SET_ID` (`attribute_set_id`),
	INDEX `IDX_CATALOG_PRODUCT_ENTITY_SKU` (`sku`),
	CONSTRAINT `FK_CAT_PRD_ENTT_ATTR_SET_ID_EAV_ATTR_SET_ATTR_SET_ID` FOREIGN KEY (`attribute_set_id`) REFERENCES `eav_attribute_set` (`attribute_set_id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COMMENT='Catalog Product Table'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;

INSERT INTO `catalog_product_entity` VALUES
(1, 4, 4, 'simple', 'SimpleProduct', 1, 1, '2016-07-13 12:31:52', '2016-07-13 12:34:07'),
(2, 4, 4, 'virtual', 'Virtual Product', 0, 0, '2016-07-13 12:34:53', '2016-07-13 12:34:53');

CREATE TABLE `core_website` (
  `website_id` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Website Id',
  `code` VARCHAR(32) NULL DEFAULT NULL COMMENT 'Code',
  `name` VARCHAR(64) NULL DEFAULT NULL COMMENT 'Website Name',
  `sort_order` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Sort Order',
  `default_group_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Default Group Id',
  `is_default` SMALLINT(5) UNSIGNED NULL DEFAULT '0' COMMENT 'Defines Is Website Default',
  `is_staging` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Is Staging Flag',
  `master_login` VARCHAR(40) NULL DEFAULT NULL COMMENT 'Master Login',
  `master_password` VARCHAR(100) NULL DEFAULT NULL COMMENT 'Master Password',
  `visibility` VARCHAR(40) NULL DEFAULT NULL COMMENT 'Visibility',
  PRIMARY KEY (`website_id`),
  UNIQUE INDEX `UNQ_CORE_WEBSITE_CODE` (`code`),
  INDEX `IDX_CORE_WEBSITE_SORT_ORDER` (`sort_order`),
  INDEX `IDX_CORE_WEBSITE_DEFAULT_GROUP_ID` (`default_group_id`)
)
  COMMENT='Websites'
  COLLATE='utf8_general_ci'
  ENGINE=InnoDB
;
INSERT INTO `core_website` (`website_id`, `code`, `name`, `sort_order`, `default_group_id`, `is_default`, `is_staging`, `master_login`, `master_password`, `visibility`) VALUES (0, 'admin', 'Admin', 0, 0, 0, 0, '', '', '');
INSERT INTO `core_website` (`website_id`, `code`, `name`, `sort_order`, `default_group_id`, `is_default`, `is_staging`, `master_login`, `master_password`, `visibility`) VALUES (1, 'base', 'Main Website', 0, 1, 1, 0, '', '', '');

CREATE TABLE `core_store_group` (
  `group_id` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Group Id',
  `website_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Website Id',
  `name` VARCHAR(255) NOT NULL COMMENT 'Store Group Name',
  `root_category_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Root Category Id',
  `default_store_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Default Store Id',
  PRIMARY KEY (`group_id`),
  INDEX `IDX_CORE_STORE_GROUP_WEBSITE_ID` (`website_id`),
  INDEX `IDX_CORE_STORE_GROUP_DEFAULT_STORE_ID` (`default_store_id`),
  CONSTRAINT `FK_CORE_STORE_GROUP_WEBSITE_ID_CORE_WEBSITE_WEBSITE_ID` FOREIGN KEY (`website_id`) REFERENCES `core_website` (`website_id`) ON UPDATE CASCADE ON DELETE CASCADE
)
  COMMENT='Store Groups'
  COLLATE='utf8_general_ci'
  ENGINE=InnoDB
;
INSERT INTO `core_store_group` (`group_id`, `website_id`, `name`, `root_category_id`, `default_store_id`) VALUES (0, 0, 'Default', 0, 0);
INSERT INTO `core_store_group` (`group_id`, `website_id`, `name`, `root_category_id`, `default_store_id`) VALUES (1, 1, 'First Main Store', 2, 1);

DROP TABLE IF EXISTS `core_store`;
CREATE TABLE `core_store` (
	`store_id` SMALLINT(5) UNSIGNED COMMENT 'Store Id',
	`code` VARCHAR(32) NULL DEFAULT NULL COMMENT 'Code',
	`website_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Website Id',
	`group_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Group Id',
	`name` VARCHAR(255) NOT NULL COMMENT 'Store Name',
	`sort_order` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Store Sort Order',
	`is_active` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Store Activity',
	PRIMARY KEY (`store_id`),
	UNIQUE INDEX `UNQ_CORE_STORE_CODE` (`code`),
	INDEX `IDX_CORE_STORE_WEBSITE_ID` (`website_id`),
	INDEX `IDX_CORE_STORE_IS_ACTIVE_SORT_ORDER` (`is_active`, `sort_order`),
	INDEX `IDX_CORE_STORE_GROUP_ID` (`group_id`),
	CONSTRAINT `FK_CORE_STORE_GROUP_ID_CORE_STORE_GROUP_GROUP_ID` FOREIGN KEY (`group_id`) REFERENCES `core_store_group` (`group_id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK_CORE_STORE_WEBSITE_ID_CORE_WEBSITE_WEBSITE_ID` FOREIGN KEY (`website_id`) REFERENCES `core_website` (`website_id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COMMENT='Stores'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;

/*!40000 ALTER TABLE `core_store` DISABLE KEYS */;
LOCK TABLES `core_store` WRITE;
/*!40000 ALTER TABLE `core_store` DISABLE KEYS */;
INSERT INTO `core_store` VALUES
(1,'admin',0,0,'Admin',0,1),
(2,'default',1,1,'Default Store View',0,1);
/*!40000 ALTER TABLE `core_store` ENABLE KEYS */;
UNLOCK TABLES;
/*!40000 ALTER TABLE `core_store` ENABLE KEYS */;


DROP TABLE IF EXISTS `core_url_rewrite`;
CREATE TABLE `core_url_rewrite` (
	`url_rewrite_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Rewrite Id',
	`store_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Store Id',
	`category_id` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'Category Id',
	`product_id` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'Product Id',
	`id_path` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Id Path',
	`request_path` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Request Path',
	`target_path` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Target Path',
	`is_system` SMALLINT(5) UNSIGNED NULL DEFAULT '1' COMMENT 'Defines is Rewrite System',
	`options` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Options',
	`description` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Description',
	PRIMARY KEY (`url_rewrite_id`),
	UNIQUE INDEX `UNQ_CORE_URL_REWRITE_REQUEST_PATH_STORE_ID` (`request_path`, `store_id`),
	UNIQUE INDEX `UNQ_CORE_URL_REWRITE_ID_PATH_IS_SYSTEM_STORE_ID` (`id_path`, `is_system`, `store_id`),
	INDEX `IDX_CORE_URL_REWRITE_TARGET_PATH_STORE_ID` (`target_path`, `store_id`),
	INDEX `IDX_CORE_URL_REWRITE_ID_PATH` (`id_path`),
	INDEX `IDX_CORE_URL_REWRITE_STORE_ID` (`store_id`),
	INDEX `FK_CORE_URL_REWRITE_PRODUCT_ID_CATALOG_PRODUCT_ENTITY_ENTITY_ID` (`product_id`),
	INDEX `FK_CORE_URL_REWRITE_CTGR_ID_CAT_CTGR_ENTT_ENTT_ID` (`category_id`),
	CONSTRAINT `FK_CORE_URL_REWRITE_CTGR_ID_CAT_CTGR_ENTT_ENTT_ID` FOREIGN KEY (`category_id`) REFERENCES `catalog_category_entity` (`entity_id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK_CORE_URL_REWRITE_PRODUCT_ID_CATALOG_PRODUCT_ENTITY_ENTITY_ID` FOREIGN KEY (`product_id`) REFERENCES `catalog_product_entity` (`entity_id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK_CORE_URL_REWRITE_STORE_ID_CORE_STORE_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COMMENT='Url Rewrites'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (3, 1, 3, 1, 'product/1/3', NULL, 'catalog/product/view/id/1/category/3', 1, '', NULL);
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (4, 1, 1, 1, 'product/1', NULL, 'catalog/product/view/id/1', 1, '', NULL);
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (5, 2, 3, 1, 'product/1/3', NULL, 'catalog/product/view/id/1/category/3', 1, '', NULL);
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (6, 2, 1, 1, 'product/1', NULL, 'catalog/product/view/id/1', 1, '', NULL);
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (7, 1, 3, 2, 'product/2/3', 'newcat/virtual-product.html', 'catalog/product/view/id/2/category/3', 1, '', NULL);
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (8, 1, 1, 2, 'product/2', 'virtual-product.html', 'catalog/product/view/id/2', 1, '', NULL);
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (9, 2, 3, 2, 'product/2/3', 'newcat/virtual-product.html', 'catalog/product/view/id/2/category/3', 1, '', NULL);
INSERT INTO `core_url_rewrite` (`url_rewrite_id`, `store_id`, `category_id`, `product_id`, `id_path`, `request_path`, `target_path`, `is_system`, `options`, `description`) VALUES (10, 2, 1, 2, 'product/2', 'virtual-product.html', 'catalog/product/view/id/2', 1, '', NULL);

DROP TABLE IF EXISTS `cms_page`;
CREATE TABLE `cms_page` (
	`page_id` SMALLINT(6) NOT NULL AUTO_INCREMENT COMMENT 'Page ID',
	`title` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Page Title',
	`root_template` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Page Template',
	`meta_keywords` TEXT NULL COMMENT 'Page Meta Keywords',
	`meta_description` TEXT NULL COMMENT 'Page Meta Description',
	`identifier` VARCHAR(100) NOT NULL COMMENT 'Page String Identifier',
	`content_heading` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Page Content Heading',
	`content` MEDIUMTEXT NULL COMMENT 'Page Content',
	`creation_time` TIMESTAMP NULL DEFAULT NULL COMMENT 'Page Creation Time',
	`update_time` TIMESTAMP NULL DEFAULT NULL COMMENT 'Page Modification Time',
	`is_active` SMALLINT(6) NOT NULL DEFAULT '1' COMMENT 'Is Page Active',
	`sort_order` SMALLINT(6) NOT NULL DEFAULT '0' COMMENT 'Page Sort Order',
	`layout_update_xml` TEXT NULL COMMENT 'Page Layout Update Content',
	`custom_theme` VARCHAR(100) NULL DEFAULT NULL COMMENT 'Page Custom Theme',
	`custom_root_template` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Page Custom Template',
	`custom_layout_update_xml` TEXT NULL COMMENT 'Page Custom Layout Update Content',
	`custom_theme_from` DATE NULL DEFAULT NULL COMMENT 'Page Custom Theme Active From Date',
	`custom_theme_to` DATE NULL DEFAULT NULL COMMENT 'Page Custom Theme Active To Date',
	`published_revision_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Published Revision Id',
	`website_root` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Website Root',
	`under_version_control` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Under Version Control Flag',
	PRIMARY KEY (`page_id`),
	INDEX `IDX_CMS_PAGE_IDENTIFIER` (`identifier`)
)
COMMENT='CMS Page Table'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
INSERT INTO `cms_page` VALUES
(1, '404 Not Found 1', 'two_columns_right', 'Page keywords', 'Page description', 'no-route', '', '<div class="page-head-alt"><h3>We’re sorry, the page you’re looking for can not be found.</h3></div>\n<div>\n    <ul class="disc">\n        <li>If you typed the URL directly, please make sure the spelling is correct.</li>\n        <li>If you clicked on a link to get here, we must have moved the content.<br/>Please try our store search box above to search for an item.</li>\n        <li>If you are not sure how you got here, <a href="#" onclick="history.go(-1);">go back</a> to the previous page</a> or return to our <a href="{{store url=""}}">store homepage</a>.</li>\n    </ul>\n</div>\n\n<!-- <div class="page-head-alt"><h3>Whoops, our bad...</h3></div>\r\n<dl>\r\n<dt>The page you requested was not found, and we have a fine guess why.</dt>\r\n<dd>\r\n<ul class="disc">\r\n<li>If you typed the URL directly, please make sure the spelling is correct.</li>\r\n<li>If you clicked on a link to get here, the link is outdated.</li>\r\n</ul></dd>\r\n</dl>\r\n<br/>\r\n<dl>\r\n<dt>What can you do?</dt>\r\n<dd>Have no fear, help is near! There are many ways you can get back on track with Magento Demo Store.</dd>\r\n<dd>\r\n<ul class="disc">\r\n<li><a href="#" onclick="history.go(-1);">Go back</a> to the previous page.</li>\r\n<li>Use the search bar at the top of the page to search for your products.</li>\r\n<li>Follow these links to get you back on track!<br/><a href="{{store url=""}}">Store Home</a><br/><a href="{{store url="customer/account"}}">My Account</a></li></ul></dd></dl><br/>\r\n<p><img src="{{skin url=\'images/media/404_callout1.jpg\'}}" style="margin-right:15px;"/><img src="{{skin url=\'images/media/404_callout2.jpg\'}}" /></p> -->', '2007-06-20 18:38:32', '2007-08-26 19:11:13', 1, 0, NULL, NULL, '', NULL, NULL, NULL, 1, 1, 0);

DROP TABLE IF EXISTS `cms_page_store`;
CREATE TABLE `cms_page_store` (
	`page_id` SMALLINT(6) NOT NULL COMMENT 'Page ID',
	`store_id` SMALLINT(5) UNSIGNED NOT NULL COMMENT 'Store ID',
	PRIMARY KEY (`page_id`, `store_id`),
	INDEX `IDX_CMS_PAGE_STORE_STORE_ID` (`store_id`),
	CONSTRAINT `FK_CMS_PAGE_STORE_PAGE_ID_CMS_PAGE_PAGE_ID` FOREIGN KEY (`page_id`) REFERENCES `cms_page` (`page_id`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `FK_CMS_PAGE_STORE_STORE_ID_CORE_STORE_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON UPDATE CASCADE ON DELETE CASCADE
)
COMMENT='CMS Page To Store Linkage Table'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
INSERT INTO `cms_page_store` VALUES (1, 1);