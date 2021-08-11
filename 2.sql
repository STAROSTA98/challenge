-- MariaDB dump 10.19  Distrib 10.4.20-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: test_samson
-- ------------------------------------------------------
-- Server version	10.4.20-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `a_category`
--

DROP TABLE IF EXISTS `a_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `a_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kod` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `parent_kod` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kod` (`kod`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `a_category`
--

LOCK TABLES `a_category` WRITE;
/*!40000 ALTER TABLE `a_category` DISABLE KEYS */;
INSERT INTO `a_category` VALUES (48,100,'Бумага',0),(49,100,'Бумага',0),(50,1,'Офисная техника',0),(51,2,'Печатающие устройства',1),(52,3,'Принтеры',2),(53,4,'МФУ',2),(54,1,'Офисная техника',0),(55,2,'Печатающие устройства',1),(56,3,'Принтеры',2);
/*!40000 ALTER TABLE `a_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `a_price`
--

DROP TABLE IF EXISTS `a_price`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `a_price` (
  `tovar` int(11) NOT NULL,
  `type_price` varchar(50) NOT NULL,
  `price` int(11) NOT NULL,
  KEY `a_price` (`tovar`),
  CONSTRAINT `a_price_ibfk_1` FOREIGN KEY (`tovar`) REFERENCES `a_product` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `a_price`
--

LOCK TABLES `a_price` WRITE;
/*!40000 ALTER TABLE `a_price` DISABLE KEYS */;
INSERT INTO `a_price` VALUES (200,'Базовая',12),(200,'Москва',13),(201,'Базовая',19),(201,'Москва',23),(202,'Базовая',3010),(202,'Москва',3500),(203,'Базовая',3310),(203,'Москва',2999);
/*!40000 ALTER TABLE `a_price` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `a_product`
--

DROP TABLE IF EXISTS `a_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `a_product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kod` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kod` (`kod`)
) ENGINE=InnoDB AUTO_INCREMENT=204 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `a_product`
--

LOCK TABLES `a_product` WRITE;
/*!40000 ALTER TABLE `a_product` DISABLE KEYS */;
INSERT INTO `a_product` VALUES (200,201,'Бумага А4'),(201,202,'Бумага А3'),(202,302,'Принтер Canon'),(203,305,'Принтер HP');
/*!40000 ALTER TABLE `a_product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `a_property`
--

DROP TABLE IF EXISTS `a_property`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `a_property` (
  `tovar` int(11) NOT NULL,
  `propety` text DEFAULT NULL,
  KEY `id_tovar` (`tovar`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `a_property`
--

LOCK TABLES `a_property` WRITE;
/*!40000 ALTER TABLE `a_property` DISABLE KEYS */;
INSERT INTO `a_property` VALUES (200,'{\"Плотность\":\"100\",\"Белизна\":\"150\"}'),(201,'{\"Плотность\":\"90\",\"Белизна\":\"100\"}'),(202,'{\"Формат\":[\"A4\",\"A3\"],\"Тип\":\"Лазерный\"}'),(203,'{\"Формат\":\"A3\",\"Тип\":\"Лазерный\"}');
/*!40000 ALTER TABLE `a_property` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_category`
--

DROP TABLE IF EXISTS `product_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_category` (
  `tovar` int(11) NOT NULL,
  `category` int(11) NOT NULL,
  KEY `tovar` (`tovar`),
  KEY `category` (`category`),
  CONSTRAINT `product_category_ibfk_3` FOREIGN KEY (`tovar`) REFERENCES `a_product` (`kod`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `product_category_ibfk_4` FOREIGN KEY (`category`) REFERENCES `a_category` (`kod`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_category`
--

LOCK TABLES `product_category` WRITE;
/*!40000 ALTER TABLE `product_category` DISABLE KEYS */;
INSERT INTO `product_category` VALUES (201,100),(202,100),(302,1),(302,2),(302,3),(302,4),(305,1),(305,2),(305,3);
/*!40000 ALTER TABLE `product_category` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2021-08-11 14:53:40
