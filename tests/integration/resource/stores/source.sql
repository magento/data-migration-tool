/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

CREATE TABLE `core_store` (
	`store_id` SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Store Id',
	`code` VARCHAR(32) NULL DEFAULT NULL COMMENT 'Code',
	`website_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Website Id',
	`group_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Group Id',
	`name` VARCHAR(255) NOT NULL COMMENT 'Store Name',
	`sort_order` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Store Sort Order',
	`is_active` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Store Activity',
	PRIMARY KEY (`store_id`)
);
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
INSERT INTO `core_store` (`store_id`, `code`, `website_id`, `group_id`, `name`, `sort_order`, `is_active`) VALUES (0, 'admin', 0, 0, 'Admin', 0, 1);
INSERT INTO `core_store` (`store_id`, `code`, `website_id`, `group_id`, `name`, `sort_order`, `is_active`) VALUES (1, 'default', 1, 1, 'English', 0, 1);
/*!40101 SET SQL_MODE=IFNULL(@OLD_INSERT_SQL_MODE,'') */;
CREATE TABLE `core_store_group` (
	`group_id` SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Group Id',
	`website_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Website Id',
	`name` VARCHAR(255) NOT NULL COMMENT 'Store Group Name',
	`root_category_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Root Category Id',
	`default_store_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Default Store Id',
  PRIMARY KEY (`group_id`)
);
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
INSERT INTO `core_store_group` (`group_id`, `website_id`, `name`, `root_category_id`, `default_store_id`) VALUES (0, 0, 'Default', 0, 0);
INSERT INTO `core_store_group` (`group_id`, `website_id`, `name`, `root_category_id`, `default_store_id`) VALUES (1, 1, 'First Main Store', 2, 1);
/*!40101 SET SQL_MODE=IFNULL(@OLD_INSERT_SQL_MODE,'') */;
CREATE TABLE `core_website` (
	`website_id` SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Website Id',
	`code` VARCHAR(32) NULL DEFAULT NULL COMMENT 'Code',
	`name` VARCHAR(64) NULL DEFAULT NULL COMMENT 'Website Name',
	`sort_order` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Sort Order',
	`default_group_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Default Group Id',
	`is_default` SMALLINT(5) UNSIGNED NULL DEFAULT '0' COMMENT 'Defines Is Website Default',
	`is_staging` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Is Staging Flag',
	`master_login` VARCHAR(40) NULL DEFAULT NULL COMMENT 'Master Login',
	`master_password` VARCHAR(100) NULL DEFAULT NULL COMMENT 'Master Password',
	`visibility` VARCHAR(40) NULL DEFAULT NULL COMMENT 'Visibility',
	PRIMARY KEY (`website_id`)
);
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
INSERT INTO `core_website` (`website_id`, `code`, `name`, `sort_order`, `default_group_id`, `is_default`, `is_staging`, `master_login`, `master_password`, `visibility`) VALUES (0, 'admin', 'Admin', 0, 0, 0, 0, '', '', '');
INSERT INTO `core_website` (`website_id`, `code`, `name`, `sort_order`, `default_group_id`, `is_default`, `is_staging`, `master_login`, `master_password`, `visibility`) VALUES (1, 'base', 'Main Website', 0, 1, 1, 0, '', '', '');
/*!40101 SET SQL_MODE=IFNULL(@OLD_INSERT_SQL_MODE,'') */;