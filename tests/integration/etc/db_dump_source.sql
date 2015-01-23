/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for table magento.eav_entity_type
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
  `increment_model` varchar(255) DEFAULT '' COMMENT 'Increment Model',
  `increment_per_store` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Increment Per Store',
  `increment_pad_length` smallint(5) unsigned NOT NULL DEFAULT '8' COMMENT 'Increment Pad Length',
  `increment_pad_char` varchar(1) NOT NULL DEFAULT '0' COMMENT 'Increment Pad Char',
  `additional_attribute_table` varchar(255) DEFAULT '' COMMENT 'Additional Attribute Table',
  `entity_attribute_collection` varchar(255) DEFAULT NULL COMMENT 'Entity Attribute Collection',
  PRIMARY KEY (`entity_type_id`),
  KEY `IDX_EAV_ENTITY_TYPE_ENTITY_TYPE_CODE` (`entity_type_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Eav Entity Type';

-- Data exporting was unselected.

-- Dumping structure for table magento.eav_attribute_set
DROP TABLE IF EXISTS `eav_attribute_set`;
CREATE TABLE IF NOT EXISTS `eav_attribute_set` (
  `attribute_set_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Attribute Set Id',
  `entity_type_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Entity Type Id',
  `attribute_set_name` varchar(255) DEFAULT NULL COMMENT 'Attribute Set Name',
  `sort_order` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Sort Order',
  PRIMARY KEY (`attribute_set_id`),
  UNIQUE KEY `UNQ_EAV_ATTRIBUTE_SET_ENTITY_TYPE_ID_ATTRIBUTE_SET_NAME` (`entity_type_id`,`attribute_set_name`),
  KEY `IDX_EAV_ATTRIBUTE_SET_ENTITY_TYPE_ID_SORT_ORDER` (`entity_type_id`,`sort_order`),
  CONSTRAINT `FK_EAV_ATTR_SET_ENTT_TYPE_ID_EAV_ENTT_TYPE_ENTT_TYPE_ID` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Eav Attribute Set';

-- Data exporting was unselected.

-- Dumping structure for table magento.catalog_product_entity
DROP TABLE IF EXISTS `catalog_product_entity`;
CREATE TABLE IF NOT EXISTS `catalog_product_entity` (
  `entity_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Entity ID',
  `entity_type_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Entity Type ID',
  `attribute_set_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Attribute Set ID',
  `type_id` varchar(32) NOT NULL DEFAULT 'simple' COMMENT 'Type ID',
  `sku` varchar(64) DEFAULT NULL COMMENT 'SKU',
  `has_options` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Has Options',
  `required_options` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Required Options',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Creation Time',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'Update Time',
  PRIMARY KEY (`entity_id`),
  KEY `IDX_CATALOG_PRODUCT_ENTITY_ENTITY_TYPE_ID` (`entity_type_id`),
  KEY `IDX_CATALOG_PRODUCT_ENTITY_ATTRIBUTE_SET_ID` (`attribute_set_id`),
  KEY `IDX_CATALOG_PRODUCT_ENTITY_SKU` (`sku`),
  CONSTRAINT `FK_CAT_PRD_ENTT_ATTR_SET_ID_EAV_ATTR_SET_ATTR_SET_ID` FOREIGN KEY (`attribute_set_id`) REFERENCES `eav_attribute_set` (`attribute_set_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CAT_PRD_ENTT_ENTT_TYPE_ID_EAV_ENTT_TYPE_ENTT_TYPE_ID` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Catalog Product Table';

-- Data exporting was unselected.

DELETE FROM `catalog_product_entity`;
/*!40000 ALTER TABLE `catalog_product_entity` DISABLE KEYS */;
INSERT INTO `catalog_product_entity` (`entity_id`, `entity_type_id`, `attribute_set_id`, `type_id`, `sku`, `created_at`, `updated_at`, `has_options`, `required_options`) VALUES
	(163, 10, 9, 'bundle', 'computer', '2008-07-25 06:34:24', '2008-07-29 06:33:10', 1, 1),
	(164, 10, 9, 'bundle', 'computer_fixed', '2008-07-25 06:36:33', '2008-07-31 14:30:37', 1, 1),
	(165, 10, 39, 'bundle', 'mycomputer', '2008-07-25 06:40:27', '2008-07-31 21:19:40', 1, 1),
	(167, 10, 9, 'giftcard', '98123', '2011-02-04 04:55:20', '2012-04-27 20:08:58', 1, 1),
	(172, 10, 61, 'simple', 'MV15VESA75', '2012-04-10 10:54:59', '2014-10-22 13:07:25', 0, 0),
	(173, 10, 61, 'simple', 'MV17VESA75', '2012-04-10 13:39:55', '2014-11-27 11:19:21', 0, 0),
	(174, 10, 61, 'simple', 'MV17VESADIM', '2012-04-10 15:20:32', '2014-11-27 11:18:28', 0, 0),
	(175, 10, 61, 'simple', 'MV17SW', '2012-04-11 17:28:50', '2014-10-22 13:02:22', 0, 0),
	(177, 10, 61, 'simple', 'MV17RN', '2012-04-11 18:16:39', '2014-10-22 13:01:16', 0, 0),
	(178, 10, 61, 'simple', 'MV17BF', '2012-04-11 18:41:41', '2014-10-22 13:37:30', 0, 0),
	(179, 10, 61, 'simple', 'MV17MD', '2012-04-11 19:11:38', '2014-10-22 13:40:09', 0, 0),
	(180, 10, 61, 'simple', 'MV15T', '2012-04-12 14:58:08', '2014-10-22 12:41:38', 0, 0),
	(181, 10, 61, 'simple', 'MV17TUSB', '2012-04-12 15:18:27', '2014-10-22 12:38:35', 0, 0),
	(182, 10, 61, 'simple', 'MV19TU', '2012-04-12 16:01:00', '2014-10-22 12:40:05', 1, 0),
	(183, 10, 61, 'simple', 'PT-M-19W-CAP', '2012-11-21 16:52:17', '2014-07-18 09:35:34', 0, 0),
	(186, 10, 61, 'simple', 'PT-M-216W-CAP', '2012-11-22 21:13:24', '2013-06-20 17:47:29', 0, 0),
	(187, 10, 9, 'simple', 'PATRIOT32', '2012-11-23 12:28:21', '2013-06-20 17:46:42', 0, 0),
	(188, 10, 9, 'simple', 'Seagate_XT_ST750LX003', '2012-11-23 12:48:54', '2014-05-23 20:01:29', 0, 0),
	(190, 10, 9, 'simple', 'WD20EFRX', '2012-11-23 19:33:01', '2014-10-23 07:56:27', 1, 0),
	(197, 10, 39, 'simple', 'BC4X99EA03', '2013-03-15 19:16:56', '2013-08-28 13:36:52', 0, 0),
	(198, 10, 39, 'simple', 'BC1N10EA03', '2013-03-15 20:27:54', '2013-08-28 13:35:56', 0, 0),
	(202, 10, 9, 'simple', 'hp_g6_2240sa', '2013-07-15 08:56:31', '2013-08-28 13:30:31', 0, 0),
	(204, 10, 9, 'simple', 'T2735MSC-B1', '2013-08-29 14:53:28', '2014-10-23 08:16:01', 1, 0),
	(205, 10, 9, 'simple', 'TF2234MC-B1', '2013-08-29 15:59:30', '2014-07-16 14:24:55', 0, 0),
	(208, 10, 9, 'simple', 'W_1', '2013-10-18 12:27:57', '2014-07-16 14:22:43', 0, 0),
	(209, 10, 59, 'virtual', 'Gpix_3', '2013-10-18 12:47:04', '2014-11-27 11:15:59', 0, 0),
	(210, 10, 59, 'simple', 'Gp_5', '2013-10-18 13:18:36', '2014-11-27 11:15:37', 0, 0),
	(212, 10, 9, 'simple', 'PS_hd8325', '2013-10-18 19:14:59', '2014-07-16 14:19:53', 1, 0),
	(214, 10, 9, 'simple', 'UE32F6', '2013-10-29 16:41:48', '2014-07-16 13:55:35', 1, 1),
	(215, 10, 9, 'simple', 'Esp_phil2', '2014-06-03 12:32:21', '2014-07-10 11:35:08', 1, 0),
	(216, 10, 9, 'simple', 'Seag_SSH1', '2014-07-16 15:01:47', '2014-10-23 08:12:38', 1, 0),
	(217, 10, 61, 'simple', 'mer_build1', '2014-07-22 16:06:47', '2014-10-23 07:39:25', 0, 0),
	(218, 10, 61, 'simple', 'mer_build2', '2014-07-28 08:31:31', '2014-10-23 07:39:05', 1, 0),
	(219, 10, 61, 'simple', 'mer_build19', '2014-07-28 09:53:03', '2014-10-23 07:38:46', 1, 0),
	(221, 10, 61, 'simple', 'mer_build_22', '2014-08-21 12:42:09', '2014-10-23 07:38:24', 1, 0);


/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
