-- Progettazione Web 
DROP DATABASE if exists querci_655055; 
CREATE DATABASE querci_655055; 
USE querci_655055; 
-- MySQL dump 10.13  Distrib 5.7.28, for Win64 (x86_64)
--
-- Host: localhost    Database: querci_655055
-- ------------------------------------------------------
-- Server version	5.7.28

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
-- Table structure for table `iscritti`
--

DROP TABLE IF EXISTS `iscritti`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `iscritti` (
  `email` varchar(50) NOT NULL,
  `nome` varchar(20) NOT NULL,
  `cognome` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `dataIscrizione` date DEFAULT NULL,
  `admin` tinyint(4) DEFAULT '0',
  `dataScadenza` date DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `iscritti`
--

LOCK TABLES `iscritti` WRITE;
/*!40000 ALTER TABLE `iscritti` DISABLE KEYS */;
INSERT INTO `iscritti` VALUES ('admin@example.com','Admin','Admin','$2y$10$vRAvojdHuIAD.aW3S9CkleDNUX1nqGI5XhQXNDvphvZEkdb18NRYK',NULL,1,NULL),('utente1@example.com','Utente','Utente','$2y$10$bZw2Wr9aWXPedMP.2LoqBeWNvtgukA1qYYmy1WhIZpOGRX5NLKOiC','2025-03-28',0,'2025-04-30'),('utente2@example.com','Utente','Utente','$2y$10$mXRBuVTCoM2sLhmbZaCkvu2qFnG3lA6M83VgJUCR/0P/VqkoR5Wdq','2025-03-28',0,'2025-03-30'),('utente3@example.com','Utente','Utente','$2y$10$ksiQ1prjdmxAocH5lsD.8ukjI7Opi5bf.b17.J4.rcwk07uqARpXC','2025-03-28',0,'2025-04-30'),('utente4@example.com','Utente','Utente','$2y$10$a5Bw1nOoS2nsMPA.MtZZJOoqRhNzbi9IbjzRdmQKUsStG5SKTwnUW','2025-03-28',0,'2025-06-01'),('utente5@example.com','Utente','Utente','$2y$10$LwaIcEWKJHF19BNGqQKRi.XjE8E60mMZpeYsrcghnyTDdHdufrYs.','2025-03-28',0,'2025-03-30'),('utente6@example.com','Utente','Utente','$2y$10$/qLTxu23/PsmyVPdW4E.jOXWYism6M08CwlC.AC8UrrpSrDy1dtU2','2025-03-28',0,'2025-03-30');
/*!40000 ALTER TABLE `iscritti` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prenotazioni`
--

DROP TABLE IF EXISTS `prenotazioni`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prenotazioni` (
  `email` varchar(50) NOT NULL,
  `data` date NOT NULL,
  `ora` varchar(11) NOT NULL,
  PRIMARY KEY (`email`,`data`),
  CONSTRAINT `prenotazioni_ibfk_1` FOREIGN KEY (`email`) REFERENCES `iscritti` (`email`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prenotazioni`
--

LOCK TABLES `prenotazioni` WRITE;
/*!40000 ALTER TABLE `prenotazioni` DISABLE KEYS */;
INSERT INTO `prenotazioni` VALUES ('utente1@example.com','2025-03-29','08:00-10:00'),('utente1@example.com','2025-04-12','08:00-10:00'),('utente2@example.com','2025-03-29','08:00-10:00'),('utente3@example.com','2025-03-29','08:00-10:00'),('utente3@example.com','2025-04-12','08:00-10:00'),('utente4@example.com','2025-03-29','08:00-10:00'),('utente5@example.com','2025-03-29','10:00-12:00'),('utente6@example.com','2025-03-29','08:00-10:00');
/*!40000 ALTER TABLE `prenotazioni` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-04-06 19:15:44
