-- MySQL dump 10.13  Distrib 5.6.15, for Linux (x86_64)
--
-- Host: localhost    Database: migration_source
-- ------------------------------------------------------
-- Server version	5.6.15-56

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

--
-- Table structure for table `core_store`
--

DROP TABLE IF EXISTS `core_store`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_store` (
  `store_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Store Id',
  `code` varchar(32) DEFAULT NULL COMMENT 'Code',
  `website_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Website Id',
  `group_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Group Id',
  `name` varchar(255) NOT NULL COMMENT 'Store Name',
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Store Sort Order',
  `is_active` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Store Activity',
  PRIMARY KEY (`store_id`),
  UNIQUE KEY `UNQ_CORE_STORE_CODE` (`code`),
  KEY `IDX_CORE_STORE_WEBSITE_ID` (`website_id`),
  KEY `IDX_CORE_STORE_IS_ACTIVE_SORT_ORDER` (`is_active`,`sort_order`),
  KEY `IDX_CORE_STORE_GROUP_ID` (`group_id`),
  CONSTRAINT `FK_CORE_STORE_GROUP_ID_CORE_STORE_GROUP_GROUP_ID` FOREIGN KEY (`group_id`) REFERENCES `core_store_group` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CORE_STORE_WEBSITE_ID_CORE_WEBSITE_WEBSITE_ID` FOREIGN KEY (`website_id`) REFERENCES `core_website` (`website_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='Stores';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_store`
--

LOCK TABLES `core_store` WRITE;
/*!40000 ALTER TABLE `core_store` DISABLE KEYS */;
INSERT INTO `core_store` VALUES
(0,'admin',0,0,'Admin',0,1),
(1,'default',1,1,'Default Store View',0,1),
(2,'de',1,1,'German',0,1),
(3,'mw_store_02',1,2,'MWStore View02',0,1);
/*!40000 ALTER TABLE `core_store` ENABLE KEYS */;
UNLOCK TABLES;


--
-- Table structure for table `core_config_data`
--

DROP TABLE IF EXISTS `core_config_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_config_data` (
  `config_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Config Id',
  `scope` varchar(8) NOT NULL DEFAULT 'default' COMMENT 'Config Scope',
  `scope_id` int(11) NOT NULL DEFAULT '0' COMMENT 'Config Scope Id',
  `path` varchar(255) NOT NULL DEFAULT 'general' COMMENT 'Config Path',
  `value` text COMMENT 'Config Value',
  PRIMARY KEY (`config_id`),
  UNIQUE KEY `UNQ_CORE_CONFIG_DATA_SCOPE_SCOPE_ID_PATH` (`scope`,`scope_id`,`path`)
) ENGINE=InnoDB AUTO_INCREMENT=121 DEFAULT CHARSET=utf8 COMMENT='Config Data';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_config_data`
--

LOCK TABLES `core_config_data` WRITE;
/*!40000 ALTER TABLE `core_config_data` DISABLE KEYS */;
INSERT INTO `core_config_data` VALUES
(1,'default',0,'catalog/seo/product_url_suffix','html'),
(2,'default',0,'catalog/seo/category_url_suffix','html'),
(3,'websites',1,'catalog/seo/product_url_suffix','html1'),
(4,'stores',3,'catalog/seo/product_url_suffix','html2');
/*!40000 ALTER TABLE `core_config_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enterprise_url_rewrite`
--

DROP TABLE IF EXISTS `enterprise_url_rewrite`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enterprise_url_rewrite` (
  `url_rewrite_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Url Rewrite Id',
  `request_path` varchar(255) NOT NULL COMMENT 'Request Path',
  `target_path` varchar(255) NOT NULL COMMENT 'Target path',
  `is_system` smallint(5) unsigned NOT NULL COMMENT 'Is url rewrite System',
  `guid` varchar(32) NOT NULL COMMENT 'GUID',
  `identifier` varchar(255) NOT NULL COMMENT 'Unique url identifier',
  `inc` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'Url increment',
  `value_id` int(10) unsigned NOT NULL COMMENT 'Entity table identifier',
  `store_id` smallint(5) unsigned NOT NULL COMMENT 'Store Id',
  `entity_type` smallint(5) unsigned NOT NULL COMMENT 'Url Rewrite Entity Type',
  PRIMARY KEY (`url_rewrite_id`),
  UNIQUE KEY `UNQ_ENTERPRISE_URL_REWRITE_REQUEST_PATH_STORE_ID_ENTITY_TYPE` (`request_path`,`store_id`,`entity_type`),
  KEY `IDX_ENTERPRISE_URL_REWRITE_IDENTIFIER` (`identifier`),
  KEY `IDX_ENTERPRISE_URL_REWRITE_VALUE_ID_GUID` (`value_id`,`guid`)
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8 COMMENT='URL Rewrite';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enterprise_url_rewrite`
--

LOCK TABLES `enterprise_url_rewrite` WRITE;
/*!40000 ALTER TABLE `enterprise_url_rewrite` DISABLE KEYS */;
INSERT INTO `enterprise_url_rewrite` VALUES
(1,'test','catalog/category/view/id/3',1,'aafdc55c2b13623895ba2f0d586f69e1','test',1,3,1,2),
(28,'test2','catalog/category/view/id/4',1,'e310bd9490c57030e520593d9eb6ce2e','test2',1,4,1,2),
(29,'test2','catalog/category/view/id/4',1,'ecb44f765c18a3af00f24128d8b6a3b8','test2',1,4,2,2),
(30,'test','catalog/category/view/id/3',1,'962b97814939018fb8477ca2e7e03c35','test',1,3,2,2),
(33,'test2/test3','catalog/category/view/id/5',1,'e0afb234ba3561cdbaba88938a562a4b','test2/test3',1,5,1,2),
(34,'test2/test3','catalog/category/view/id/5',1,'9b0dfaa6bee6bf08eabe6b185fdf2df8','test2/test3',1,5,2,2),
(35,'test','catalog/category/view/id/3',1,'c8cab577a8c630cd873427bb6b90f96d','test',1,3,3,2),
(36,'test2','catalog/category/view/id/4',1,'10eaef8757f416c135a609dbec41ea77','test2',1,4,3,2),
(37,'test2/test3','catalog/category/view/id/5',1,'ed3ccf135ec704e3df20648b5ae7b8b0','test2/test3',1,5,3,2),
(46,'test','catalog/product/view/id/4',1,'79cd63a34f1f50f3a38cda4ef9591534','test',1,10,0,3),
(47,'test-product','catalog/product/view/id/4',1,'79cd63a34f1f50f3a38cda4ef9591534','test-product',1,11,3,3),
(48,'test-store-first','catalog/product/view/id/4',1,'79cd63a34f1f50f3a38cda4ef9591534','test-store-first',1,12,1,3),
(49,'test1.html','contacts',0,'4a0c0b790c2cb138ef699611f922339b','test1.html',1,1,1,1),
(50,'test2/test3/test_product.html','catalog/product/view/id/4/category/5',0,'4a0c0b790c2cb138ef699611f922339b','test2/test3/test_product.html',1,2,1,1),
(52,'test1','catalog/category/view/id/6',1,'a5d86e86af7f3b8b870bd5378d5084f2','test1',1,6,1,2),
(53,'test1','catalog/category/view/id/6',1,'69c325cf7b8a05180bda36bc511c1cce','test1',1,6,2,2),
(54,'test1','catalog/category/view/id/6',1,'f7011308e6f8f4e431bca3efdd1026f0','test1',1,6,3,2),
(55,'test1','catalog/product/view/id/5',1,'ada15e3563bf79b2d5c67d5cd5069e68','test1',1,13,0,3),
(56,'test4','catalog/category/view/id/7',1,'bd0ccc57528c1f3f086e6a82b83741a6','test4',1,7,1,2),
(57,'test4','catalog/category/view/id/7',1,'6eff0e8bfa846138f11cfb4a7bf5410d','test4',1,7,2,2),
(58,'test4','catalog/category/view/id/7',1,'d7f037bfe70bc4681d129bc7f5dcc76d','test4',1,7,3,2),
(59,'test5.html','contacts',0,'941ecaa436b59edf3c32e6f58c666697','test5.html',1,6,1,1),
(60,'test5','catalog/category/view/id/8',1,'74c8106e92344a3dcbf5732d6d8534fd','test5',1,8,1,2),
(61,'test5','catalog/category/view/id/8',1,'6d98585d20ddb1677a4e6458dabfe6be','test5',1,8,2,2),
(62,'test5','catalog/category/view/id/8',1,'8375e1e3b337311dde8a4b84e5e05c3f','test5',1,8,3,2);
/*!40000 ALTER TABLE `enterprise_url_rewrite` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enterprise_url_rewrite_redirect`
--

DROP TABLE IF EXISTS `enterprise_url_rewrite_redirect`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `enterprise_url_rewrite_redirect` (
  `redirect_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Redirect Id',
  `identifier` varchar(255) NOT NULL COMMENT 'Url identifier',
  `target_path` varchar(255) NOT NULL COMMENT 'Target path',
  `options` varchar(255) DEFAULT NULL COMMENT 'Redirect options',
  `description` varchar(255) DEFAULT NULL COMMENT 'Description',
  `category_id` int(10) unsigned DEFAULT NULL COMMENT 'Category Id',
  `product_id` int(10) unsigned DEFAULT NULL COMMENT 'Product Id',
  `store_id` smallint(5) unsigned NOT NULL COMMENT 'Store Id',
  PRIMARY KEY (`redirect_id`),
  UNIQUE KEY `UNQ_ENTERPRISE_URL_REWRITE_REDIRECT_IDENTIFIER_STORE_ID` (`identifier`,`store_id`),
  KEY `FK_ENT_URL_REWRITE_REDIRECT_CTGR_ID_CAT_CTGR_ENTT_ENTT_ID` (`category_id`),
  KEY `FK_ENT_URL_REWRITE_REDIRECT_PRD_ID_CAT_PRD_ENTT_ENTT_ID` (`product_id`),
  CONSTRAINT `FK_ENT_URL_REWRITE_REDIRECT_CTGR_ID_CAT_CTGR_ENTT_ENTT_ID` FOREIGN KEY (`category_id`) REFERENCES `catalog_category_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_ENT_URL_REWRITE_REDIRECT_PRD_ID_CAT_PRD_ENTT_ENTT_ID` FOREIGN KEY (`product_id`) REFERENCES `catalog_product_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='Permanent redirect';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enterprise_url_rewrite_redirect`
--

LOCK TABLES `enterprise_url_rewrite_redirect` WRITE;
/*!40000 ALTER TABLE `enterprise_url_rewrite_redirect` DISABLE KEYS */;
INSERT INTO `enterprise_url_rewrite_redirect` VALUES
(1,'test1.html','catalog/category/view/id/6','RP',NULL,6,NULL,1),
(2,'test2/test3/test_product.html','catalog/product/view/id/4/category/5','RP',NULL,5,4,1),
(3,'test1.html','catalog/category/view/id/6',NULL,NULL,6,NULL,2),
(4,'test1.html','catalog/category/view/id/6',NULL,NULL,6,NULL,3),
(6,'test5.html','contacts','RP',NULL,NULL,NULL,1);
/*!40000 ALTER TABLE `enterprise_url_rewrite_redirect` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catalog_category_entity_url_key`
--

DROP TABLE IF EXISTS `catalog_category_entity_url_key`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `catalog_category_entity_url_key` (
  `value_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Value ID',
  `entity_type_id` smallint(5) unsigned NOT NULL COMMENT 'Entity Type ID',
  `attribute_id` smallint(5) unsigned NOT NULL COMMENT 'Attribute ID',
  `store_id` smallint(5) unsigned NOT NULL COMMENT 'Store ID',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Entity ID',
  `value` varchar(255) DEFAULT NULL COMMENT 'Category Url Key',
  PRIMARY KEY (`value_id`),
  UNIQUE KEY `UNQ_CATALOG_CATEGORY_ENTITY_URL_KEY_ENTITY_ID_STORE_ID` (`entity_id`,`store_id`),
  KEY `IDX_CATALOG_CATEGORY_ENTITY_URL_KEY_ATTRIBUTE_ID` (`attribute_id`),
  KEY `IDX_CATALOG_CATEGORY_ENTITY_URL_KEY_STORE_ID` (`store_id`),
  KEY `IDX_CATALOG_CATEGORY_ENTITY_URL_KEY_ENTITY_ID` (`entity_id`),
  CONSTRAINT `FK_CATALOG_CATEGORY_ENTITY_URL_KEY_STORE_ID_CORE_STORE_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CAT_CTGR_ENTT_URL_KEY_ATTR_ID_EAV_ATTR_ATTR_ID` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CAT_CTGR_ENTT_URL_KEY_ENTT_ID_CAT_CTGR_ENTT_ENTT_ID` FOREIGN KEY (`entity_id`) REFERENCES `catalog_category_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COMMENT='Catalog Category Url Key Attribute Backend Table';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalog_category_entity_url_key`
--

LOCK TABLES `catalog_category_entity_url_key` WRITE;
/*!40000 ALTER TABLE `catalog_category_entity_url_key` DISABLE KEYS */;
INSERT INTO `catalog_category_entity_url_key` VALUES
(1,3,43,1,1,'root-catalog'),
(2,3,43,1,2,'default-category'),
(3,3,43,0,3,'test'),
(4,3,43,0,4,'test2'),
(5,3,43,0,5,'test3'),
(6,3,43,0,6,'test1'),
(7,3,43,0,7,'test4'),
(8,3,43,0,8,'test5');
/*!40000 ALTER TABLE `catalog_category_entity_url_key` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catalog_product_entity_url_key`
--

DROP TABLE IF EXISTS `catalog_product_entity_url_key`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `catalog_product_entity_url_key` (
  `value_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Value ID',
  `entity_type_id` smallint(5) unsigned NOT NULL COMMENT 'Entity Type ID',
  `attribute_id` smallint(5) unsigned NOT NULL COMMENT 'Attribute ID',
  `store_id` smallint(5) unsigned NOT NULL COMMENT 'Store ID',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Entity ID',
  `value` varchar(255) DEFAULT NULL COMMENT 'Product Url Key',
  PRIMARY KEY (`value_id`),
  UNIQUE KEY `UNQ_CAT_PRD_ENTT_URL_KEY_ENTT_ID_ATTR_ID_STORE_ID` (`entity_id`,`attribute_id`,`store_id`),
  UNIQUE KEY `UNQ_CATALOG_PRODUCT_ENTITY_URL_KEY_VALUE` (`value`),
  KEY `IDX_CATALOG_PRODUCT_ENTITY_URL_KEY_ATTRIBUTE_ID` (`attribute_id`),
  KEY `IDX_CATALOG_PRODUCT_ENTITY_URL_KEY_STORE_ID` (`store_id`),
  KEY `IDX_CATALOG_PRODUCT_ENTITY_URL_KEY_ENTITY_ID` (`entity_id`),
  CONSTRAINT `FK_CATALOG_PRODUCT_ENTITY_URL_KEY_STORE_ID_CORE_STORE_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CAT_PRD_ENTT_URL_KEY_ATTR_ID_EAV_ATTR_ATTR_ID` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CAT_PRD_ENTT_URL_KEY_ENTT_ID_CAT_PRD_ENTT_ENTT_ID` FOREIGN KEY (`entity_id`) REFERENCES `catalog_product_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COMMENT='Catalog Product Url Key Attribute Backend Table';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalog_product_entity_url_key`
--

LOCK TABLES `catalog_product_entity_url_key` WRITE;
/*!40000 ALTER TABLE `catalog_product_entity_url_key` DISABLE KEYS */;
INSERT INTO `catalog_product_entity_url_key` VALUES
(10,4,97,0,4,'test'),
(11,4,97,3,4,'test-product'),
(12,4,97,1,4,'test-store-first'),
(13,4,97,0,5,'test1');
/*!40000 ALTER TABLE `catalog_product_entity_url_key` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catalog_category_product`
--

DROP TABLE IF EXISTS `catalog_category_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `catalog_category_product` (
  `category_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Category ID',
  `product_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Product ID',
  `position` int(11) NOT NULL DEFAULT '0' COMMENT 'Position',
  PRIMARY KEY (`category_id`,`product_id`),
  KEY `IDX_CATALOG_CATEGORY_PRODUCT_PRODUCT_ID` (`product_id`),
  CONSTRAINT `FK_CAT_CTGR_PRD_CTGR_ID_CAT_CTGR_ENTT_ENTT_ID` FOREIGN KEY (`category_id`) REFERENCES `catalog_category_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CAT_CTGR_PRD_PRD_ID_CAT_PRD_ENTT_ENTT_ID` FOREIGN KEY (`product_id`) REFERENCES `catalog_product_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Catalog Product To Category Linkage Table';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalog_category_product`
--

LOCK TABLES `catalog_category_product` WRITE;
/*!40000 ALTER TABLE `catalog_category_product` DISABLE KEYS */;
INSERT INTO `catalog_category_product` VALUES
(3,4,1),
(3,5,1),
(4,4,1),
(5,4,0);
/*!40000 ALTER TABLE `catalog_category_product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catalog_product_website`
--

DROP TABLE IF EXISTS `catalog_product_website`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `catalog_product_website` (
  `product_id` int(10) unsigned NOT NULL COMMENT 'Product ID',
  `website_id` smallint(5) unsigned NOT NULL COMMENT 'Website ID',
  PRIMARY KEY (`product_id`,`website_id`),
  KEY `IDX_CATALOG_PRODUCT_WEBSITE_WEBSITE_ID` (`website_id`),
  CONSTRAINT `FK_CATALOG_PRODUCT_WEBSITE_WEBSITE_ID_CORE_WEBSITE_WEBSITE_ID` FOREIGN KEY (`website_id`) REFERENCES `core_website` (`website_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CAT_PRD_WS_PRD_ID_CAT_PRD_ENTT_ENTT_ID` FOREIGN KEY (`product_id`) REFERENCES `catalog_product_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Catalog Product To Website Linkage Table';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalog_product_website`
--

LOCK TABLES `catalog_product_website` WRITE;
/*!40000 ALTER TABLE `catalog_product_website` DISABLE KEYS */;
INSERT INTO `catalog_product_website` VALUES
(4,1),
(5,1);
/*!40000 ALTER TABLE `catalog_product_website` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catalog_product_entity_varchar`
--

DROP TABLE IF EXISTS `catalog_product_entity_varchar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `catalog_product_entity_varchar` (
  `value_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Value ID',
  `entity_type_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Entity Type ID',
  `attribute_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Attribute ID',
  `store_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Store ID',
  `entity_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Entity ID',
  `value` varchar(255) DEFAULT NULL COMMENT 'Value',
  PRIMARY KEY (`value_id`),
  UNIQUE KEY `UNQ_CAT_PRD_ENTT_VCHR_ENTT_ID_ATTR_ID_STORE_ID` (`entity_id`,`attribute_id`,`store_id`),
  KEY `IDX_CATALOG_PRODUCT_ENTITY_VARCHAR_ATTRIBUTE_ID` (`attribute_id`),
  KEY `IDX_CATALOG_PRODUCT_ENTITY_VARCHAR_STORE_ID` (`store_id`),
  KEY `IDX_CATALOG_PRODUCT_ENTITY_VARCHAR_ENTITY_ID` (`entity_id`),
  CONSTRAINT `FK_CATALOG_PRODUCT_ENTITY_VARCHAR_STORE_ID_CORE_STORE_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CAT_PRD_ENTT_VCHR_ATTR_ID_EAV_ATTR_ATTR_ID` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CAT_PRD_ENTT_VCHR_ENTT_ID_CAT_PRD_ENTT_ENTT_ID` FOREIGN KEY (`entity_id`) REFERENCES `catalog_product_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8 COMMENT='Catalog Product Varchar Attribute Backend Table';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalog_product_entity_varchar`
--

LOCK TABLES `catalog_product_entity_varchar` WRITE;
/*!40000 ALTER TABLE `catalog_product_entity_varchar` DISABLE KEYS */;
/*!40000 ALTER TABLE `catalog_product_entity_varchar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catalog_category_entity_varchar`
--

DROP TABLE IF EXISTS `catalog_category_entity_varchar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `catalog_category_entity_varchar` (
  `value_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Value ID',
  `entity_type_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Entity Type ID',
  `attribute_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Attribute ID',
  `store_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Store ID',
  `entity_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Entity ID',
  `value` varchar(255) DEFAULT NULL COMMENT 'Value',
  PRIMARY KEY (`value_id`),
  UNIQUE KEY `UNQ_CAT_CTGR_ENTT_VCHR_ENTT_TYPE_ID_ENTT_ID_ATTR_ID_STORE_ID` (`entity_type_id`,`entity_id`,`attribute_id`,`store_id`),
  KEY `IDX_CATALOG_CATEGORY_ENTITY_VARCHAR_ENTITY_ID` (`entity_id`),
  KEY `IDX_CATALOG_CATEGORY_ENTITY_VARCHAR_ATTRIBUTE_ID` (`attribute_id`),
  KEY `IDX_CATALOG_CATEGORY_ENTITY_VARCHAR_STORE_ID` (`store_id`),
  CONSTRAINT `FK_CATALOG_CATEGORY_ENTITY_VARCHAR_STORE_ID_CORE_STORE_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CAT_CTGR_ENTT_VCHR_ATTR_ID_EAV_ATTR_ATTR_ID` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CAT_CTGR_ENTT_VCHR_ENTT_ID_CAT_CTGR_ENTT_ENTT_ID` FOREIGN KEY (`entity_id`) REFERENCES `catalog_category_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8 COMMENT='Catalog Category Varchar Attribute Backend Table';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalog_category_entity_varchar`
--

LOCK TABLES `catalog_category_entity_varchar` WRITE;
/*!40000 ALTER TABLE `catalog_category_entity_varchar` DISABLE KEYS */;
INSERT INTO `catalog_category_entity_varchar` VALUES
(1,3,41,0,1,'Root Catalog'),
(2,3,41,1,1,'Root Catalog'),
(3,3,41,0,2,'Default Category');
/*!40000 ALTER TABLE `catalog_category_entity_varchar` ENABLE KEYS */;
UNLOCK TABLES;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-01-29 19:44:38
