-- MySQL dump 10.13  Distrib 5.6.15, for Linux (x86_64)
--
-- Host: localhost    Database: migration_destination
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
-- Table structure for table `common_table`
--

DROP TABLE IF EXISTS `common_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `common_table` (
  `key` int(11) NOT NULL AUTO_INCREMENT,
  `dest_field_ignored` int(11) NOT NULL DEFAULT '0',
  `common_field` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `common_table`
--

LOCK TABLES `common_table` WRITE;
/*!40000 ALTER TABLE `common_table` DISABLE KEYS */;
/*!40000 ALTER TABLE `common_table` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dest_table_ignored`
--

DROP TABLE IF EXISTS `dest_table_ignored`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dest_table_ignored` (
  `key` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dest_table_ignored`
--

LOCK TABLES `dest_table_ignored` WRITE;
/*!40000 ALTER TABLE `dest_table_ignored` DISABLE KEYS */;
/*!40000 ALTER TABLE `dest_table_ignored` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dest_table_renamed`
--

DROP TABLE IF EXISTS `dest_table_renamed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dest_table_renamed` (
  `key` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dest_table_renamed`
--

LOCK TABLES `dest_table_renamed` WRITE;
/*!40000 ALTER TABLE `dest_table_renamed` DISABLE KEYS */;
/*!40000 ALTER TABLE `dest_table_renamed` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `table_without_data`
--

DROP TABLE IF EXISTS `table_without_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `table_without_data` (
  `field1` int(11) DEFAULT NULL,
  `field2` int(11) DEFAULT NULL,
  `field3` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `table_without_data`
--

LOCK TABLES `table_without_data` WRITE;
/*!40000 ALTER TABLE `table_without_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `table_without_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `table_ignored`
--

DROP TABLE IF EXISTS `table_ignored`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `table_ignored` (
  `field1` int(11) NOT NULL AUTO_INCREMENT,
  `field2` int(11) DEFAULT NULL,
  PRIMARY KEY (`field1`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `source_table_ignored`
--

LOCK TABLES `table_ignored` WRITE;
/*!40000 ALTER TABLE `table_ignored` DISABLE KEYS */;
/*!40000 ALTER TABLE `table_ignored` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dest_table_1`
--

DROP TABLE IF EXISTS `dest_table_1`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dest_table_1` (
  `key` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dest_table_1`
--

LOCK TABLES `dest_table_1` WRITE;
/*!40000 ALTER TABLE `dest_table_1` DISABLE KEYS */;
/*!40000 ALTER TABLE `dest_table_1` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dest_table_2`
--

DROP TABLE IF EXISTS `dest_table_2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dest_table_2` (
  `key` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dest_table_2`
--

LOCK TABLES `dest_table_2` WRITE;
/*!40000 ALTER TABLE `dest_table_2` DISABLE KEYS */;
/*!40000 ALTER TABLE `dest_table_2` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `url_rewrite`
--

DROP TABLE IF EXISTS `url_rewrite`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `url_rewrite` (
  `url_rewrite_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Rewrite Id',
  `entity_type` varchar(32) NOT NULL COMMENT 'Entity type code',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Entity ID',
  `request_path` varchar(255) DEFAULT NULL COMMENT 'Request Path',
  `target_path` varchar(255) DEFAULT NULL COMMENT 'Target Path',
  `redirect_type` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Redirect Type',
  `store_id` smallint(5) unsigned NOT NULL COMMENT 'Store Id',
  `description` varchar(255) DEFAULT NULL COMMENT 'Description',
  `is_autogenerated` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Is rewrite generated automatically flag',
  `metadata` varchar(255) DEFAULT NULL COMMENT 'Meta data for url rewrite',
  PRIMARY KEY (`url_rewrite_id`),
  UNIQUE KEY `UNQ_URL_REWRITE_REQUEST_PATH_STORE_ID` (`request_path`,`store_id`),
  KEY `IDX_URL_REWRITE_TARGET_PATH` (`target_path`),
  KEY `IDX_URL_REWRITE_STORE_ID_ENTITY_ID` (`store_id`,`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Url Rewrites';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `catalog_category_entity_varchar`
--

DROP TABLE IF EXISTS `catalog_category_entity_varchar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `catalog_category_entity_varchar` (
  `value_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Value ID',
  `attribute_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Attribute ID',
  `store_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Store ID',
  `entity_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Entity ID',
  `value` varchar(255) DEFAULT NULL COMMENT 'Value',
  PRIMARY KEY (`value_id`),
  UNIQUE KEY `UNQ_CAT_CTGR_ENTT_VCHR_ENTT_TYPE_ID_ENTT_ID_ATTR_ID_STORE_ID` (`entity_id`,`attribute_id`,`store_id`),
  KEY `IDX_CATALOG_CATEGORY_ENTITY_VARCHAR_ENTITY_ID` (`entity_id`),
  KEY `IDX_CATALOG_CATEGORY_ENTITY_VARCHAR_ATTRIBUTE_ID` (`attribute_id`),
  KEY `IDX_CATALOG_CATEGORY_ENTITY_VARCHAR_STORE_ID` (`store_id`),
  CONSTRAINT `FK_CATALOG_CATEGORY_ENTITY_VARCHAR_STORE_ID_STORE_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CAT_CTGR_ENTT_VCHR_ATTR_ID_EAV_ATTR_ATTR_ID` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CAT_CTGR_ENTT_VCHR_ENTT_ID_CAT_CTGR_ENTT_ENTT_ID` FOREIGN KEY (`entity_id`) REFERENCES `catalog_category_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='Catalog Category Varchar Attribute Backend Table';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalog_category_entity_varchar`
--

LOCK TABLES `catalog_category_entity_varchar` WRITE;
/*!40000 ALTER TABLE `catalog_category_entity_varchar` DISABLE KEYS */;
INSERT INTO `catalog_category_entity_varchar` VALUES
(1,41,0,1,'Root Catalog'),
(2,41,0,2,'Default Category'),
(3,48,0,2,'PRODUCTS');
/*!40000 ALTER TABLE `catalog_category_entity_varchar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catalog_product_entity_varchar`
--

DROP TABLE IF EXISTS `catalog_product_entity_varchar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `catalog_product_entity_varchar` (
  `value_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Value ID',
  `attribute_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Attribute ID',
  `store_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Store ID',
  `entity_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Entity ID',
  `value` varchar(255) DEFAULT NULL COMMENT 'Value',
  PRIMARY KEY (`value_id`),
  UNIQUE KEY `UNQ_CAT_PRD_ENTT_VCHR_ENTT_ID_ATTR_ID_STORE_ID` (`entity_id`,`attribute_id`,`store_id`),
  KEY `IDX_CATALOG_PRODUCT_ENTITY_VARCHAR_ATTRIBUTE_ID` (`attribute_id`),
  KEY `IDX_CATALOG_PRODUCT_ENTITY_VARCHAR_STORE_ID` (`store_id`),
  CONSTRAINT `FK_CATALOG_PRODUCT_ENTITY_VARCHAR_STORE_ID_STORE_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CAT_PRD_ENTT_VCHR_ATTR_ID_EAV_ATTR_ATTR_ID` FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute` (`attribute_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_CAT_PRD_ENTT_VCHR_ENTT_ID_CAT_PRD_ENTT_ENTT_ID` FOREIGN KEY (`entity_id`) REFERENCES `catalog_product_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Catalog Product Varchar Attribute Backend Table';
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `sales_order`
--

DROP TABLE IF EXISTS `sales_order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sales_order` (
  `entity_id` int(11) DEFAULT NULL,
  `store_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales_order`
--

LOCK TABLES `sales_order` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `eav_entity_int`
--

DROP TABLE IF EXISTS `eav_entity_int`;
CREATE TABLE `eav_entity_int` (
	`value_id` INT(11) NOT NULL AUTO_INCREMENT,
	`entity_type_id` SMALLINT(5) NULL DEFAULT NULL,
	`attribute_id` SMALLINT(5) NULL DEFAULT NULL,
	`store_id` SMALLINT(5) NULL DEFAULT NULL,
	`entity_id` INT(10) NULL DEFAULT NULL,
	`value` INT(11) NULL DEFAULT NULL,
	PRIMARY KEY (`value_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `eav_entity_int` WRITE;
UNLOCK TABLES;

--
-- Table structure for table `eav_attribute`
--

DROP TABLE IF EXISTS `eav_attribute`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eav_attribute` (
  `attribute_code` VARCHAR(32) DEFAULT NULL,
	`attribute_id` SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
	`entity_type_id` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`attribute_id`),
	UNIQUE INDEX `UNQ_EAV_ATTRIBUTE_ENTITY_TYPE_ID_ATTRIBUTE_CODE` (`entity_type_id`, `attribute_code`),
	CONSTRAINT `FK_EAV_ATTRIBUTE_ENTITY_TYPE_ID_EAV_ENTITY_TYPE_ENTITY_TYPE_ID` FOREIGN KEY (`entity_type_id`) REFERENCES `eav_entity_type` (`entity_type_id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eav_attribute`
--

LOCK TABLES `eav_attribute` WRITE;
INSERT INTO `eav_attribute` VALUES ('reward_points_balance_refunded',1,1),('reward_salesrule_points',2,1);
UNLOCK TABLES;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-01-29 19:44:27
