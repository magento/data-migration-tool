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
-- Table structure for table `common_table`
--

DROP TABLE IF EXISTS `common_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `common_table` (
  `key` int(11) NOT NULL AUTO_INCREMENT,
  `common_field` int(11) DEFAULT NULL,
  `source_field_ignored` int(11) DEFAULT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `common_table`
--

LOCK TABLES `common_table` WRITE;
/*!40000 ALTER TABLE `common_table` DISABLE KEYS */;
INSERT INTO `common_table` VALUES (1,2,3),(2,3,4),(3,4,5),(4,5,6),(5,5,5),(6,6,7),(7,7,7);
/*!40000 ALTER TABLE `common_table` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `source_table_ignored`
--

DROP TABLE IF EXISTS `source_table_ignored`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `source_table_ignored` (
  `field1` int(11) NOT NULL AUTO_INCREMENT,
  `field2` int(11) DEFAULT NULL,
  PRIMARY KEY (`field1`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `source_table_ignored`
--

LOCK TABLES `source_table_ignored` WRITE;
/*!40000 ALTER TABLE `source_table_ignored` DISABLE KEYS */;
INSERT INTO `source_table_ignored` VALUES (1,2),(2,3),(3,4),(4,5),(5,5),(6,6),(7,7);
/*!40000 ALTER TABLE `source_table_ignored` ENABLE KEYS */;
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
INSERT INTO `table_ignored` VALUES (1,2),(2,3),(3,4),(4,5),(5,5),(6,6),(7,7);
/*!40000 ALTER TABLE `table_ignored` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `source_table_renamed`
--

DROP TABLE IF EXISTS `source_table_renamed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `source_table_renamed` (
  `key` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `source_table_renamed`
--

LOCK TABLES `source_table_renamed` WRITE;
/*!40000 ALTER TABLE `source_table_renamed` DISABLE KEYS */;
/*!40000 ALTER TABLE `source_table_renamed` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `table_with_data`
--

DROP TABLE IF EXISTS `table_with_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `table_with_data` (
  `field1` int(11) DEFAULT NULL,
  `field2` int(11) DEFAULT NULL,
  `field3` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `table_with_data`
--

LOCK TABLES `table_with_data` WRITE;
/*!40000 ALTER TABLE `table_with_data` DISABLE KEYS */;
INSERT INTO `table_with_data` VALUES (1,2,3),(2,3,4),(3,4,5),(4,5,6),(5,5,5),(6,6,7),(7,7,7);
/*!40000 ALTER TABLE `table_with_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `source_table_1`
--

DROP TABLE IF EXISTS `source_table_1`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `source_table_1` (
  `key` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `source_table_1`
--

LOCK TABLES `source_table_1` WRITE;
/*!40000 ALTER TABLE `source_table_1` DISABLE KEYS */;
/*!40000 ALTER TABLE `source_table_1` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `source_table_2`
--

DROP TABLE IF EXISTS `source_table_2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `source_table_2` (
  `key` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `source_table_2`
--

LOCK TABLES `source_table_2` WRITE;
/*!40000 ALTER TABLE `source_table_2` DISABLE KEYS */;
/*!40000 ALTER TABLE `source_table_2` ENABLE KEYS */;
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
