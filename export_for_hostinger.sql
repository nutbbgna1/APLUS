-- MariaDB dump 10.19  Distrib 10.4.28-MariaDB, for osx10.10 (x86_64)
--
-- Host: localhost    Database: u865886212_english
-- ------------------------------------------------------
-- Server version	10.4.28-MariaDB

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
-- Table structure for table `accounting_logs`
--

DROP TABLE IF EXISTS `accounting_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accounting_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `type` enum('income','expense') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `ref_order_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accounting_logs`
--

LOCK TABLES `accounting_logs` WRITE;
/*!40000 ALTER TABLE `accounting_logs` DISABLE KEYS */;
INSERT INTO `accounting_logs` VALUES (1,'รายรับจากคอร์ส: คอร์สอังกฤษเข้มข้น สำหรับ ป.4 (Order #1)','income',1500.00,1,'2026-07-22 08:34:40');
/*!40000 ALTER TABLE `accounting_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admin_messages`
--

DROP TABLE IF EXISTS `admin_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_name` varchar(100) NOT NULL,
  `sender_email` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_messages`
--

LOCK TABLES `admin_messages` WRITE;
/*!40000 ALTER TABLE `admin_messages` DISABLE KEYS */;
INSERT INTO `admin_messages` VALUES (1,'Alice Smith','alice@example.com','I have a question about the advanced grammar course.',0,'2026-07-22 08:55:05'),(2,'Bob Johnson','bob@example.com','Can I request a refund for my purchase last week?',0,'2026-07-22 08:55:05');
/*!40000 ALTER TABLE `admin_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admin_notifications`
--

DROP TABLE IF EXISTS `admin_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) DEFAULT 'info',
  `title` varchar(255) NOT NULL,
  `message` text DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_notifications`
--

LOCK TABLES `admin_notifications` WRITE;
/*!40000 ALTER TABLE `admin_notifications` DISABLE KEYS */;
INSERT INTO `admin_notifications` VALUES (1,'order','New Order Received','Order #1024 has been placed for English Basics.','?page=orders',0,'2026-07-22 08:55:05'),(2,'student','New Student Registered','John Doe just signed up.','?page=students',0,'2026-07-22 08:55:05'),(3,'system','System Update','LinguaMax has been updated to version 2.1.','#',0,'2026-07-22 08:55:05');
/*!40000 ALTER TABLE `admin_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `badges`
--

DROP TABLE IF EXISTS `badges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `badges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `name_th` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) NOT NULL,
  `color` varchar(7) DEFAULT '#FFB347',
  `requirement_type` varchar(50) NOT NULL,
  `requirement_value` int(11) NOT NULL,
  `xp_reward` int(11) DEFAULT 20,
  `sort_order` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `badges`
--

LOCK TABLES `badges` WRITE;
/*!40000 ALTER TABLE `badges` DISABLE KEYS */;
INSERT INTO `badges` VALUES (1,'First Steps','ก้าวแรก','เรียนบทเรียนแรกสำเร็จ','🌟','#FFD700','lessons_completed',1,10,1),(2,'Bookworm','หนอนหนังสือ','เรียนครบ 5 บทเรียน','📚','#4ECDC4','lessons_completed',5,30,2),(3,'Scholar','นักปราชญ์','เรียนครบ 10 บทเรียน','🎓','#6C63FF','lessons_completed',10,50,3),(4,'Word Collector','นักสะสมคำ','ท่องศัพท์ครบ 30 คำ','📖','#FF6B9D','vocab_learned',30,20,4),(5,'Word Master','ราชาคำศัพท์','ท่องศัพท์ครบ 100 คำ','👑','#FFB347','vocab_learned',100,50,5),(6,'Quiz Whiz','เทพข้อสอบ','สอบผ่าน 5 ครั้ง','📝','#45B7D1','exams_passed',5,30,6),(7,'Perfect Score','คะแนนเต็ม','ได้คะแนนเต็ม 100%','💯','#2ED573','perfect_score',1,50,7),(8,'On Fire','ร้อนแรง','เรียนต่อเนื่อง 3 วัน','🔥','#FF4757','streak_days',3,20,8),(9,'Unstoppable','หยุดไม่อยู่','เรียนต่อเนื่อง 7 วัน','⚡','#FFA502','streak_days',7,40,9),(10,'Champion','แชมป์เปี้ยน','เรียนต่อเนื่อง 30 วัน','🏆','#FFD700','streak_days',30,100,10),(11,'Game Pro','เซียนเกม','เล่นเกมครบ 10 ครั้ง','🎮','#A29BFE','games_played',10,30,11),(12,'Speed Reader','นักอ่านเร็ว','อ่านจบ 5 เรื่อง','📕','#E17055','readings_completed',5,30,12);
/*!40000 ALTER TABLE `badges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `course_categories`
--

DROP TABLE IF EXISTS `course_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_categories`
--

LOCK TABLES `course_categories` WRITE;
/*!40000 ALTER TABLE `course_categories` DISABLE KEYS */;
INSERT INTO `course_categories` VALUES (2,'คณิต'),(3,'วิทย์'),(5,'สังคม'),(1,'อังกฤษ'),(4,'ไทย');
/*!40000 ALTER TABLE `course_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `course_enrollments`
--

DROP TABLE IF EXISTS `course_enrollments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_enrollments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `payment_slip_url` varchar(500) DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `course_id` (`course_id`),
  CONSTRAINT `course_enrollments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `course_enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_enrollments`
--

LOCK TABLES `course_enrollments` WRITE;
/*!40000 ALTER TABLE `course_enrollments` DISABLE KEYS */;
INSERT INTO `course_enrollments` VALUES (1,8,19,'rejected',NULL,'2026-07-22 07:34:15','2026-07-22 07:34:23'),(2,8,1,'approved','linguamax/uploads/slips/slip_1784708527_502.png','2026-07-22 08:22:07','2026-07-22 08:34:40');
/*!40000 ALTER TABLE `course_enrollments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `course_episodes`
--

DROP TABLE IF EXISTS `course_episodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_episodes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `episode_number` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `video_url` varchar(500) DEFAULT NULL,
  `is_locked` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`),
  CONSTRAINT `course_episodes_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_episodes`
--

LOCK TABLES `course_episodes` WRITE;
/*!40000 ALTER TABLE `course_episodes` DISABLE KEYS */;
/*!40000 ALTER TABLE `course_episodes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `course_materials`
--

DROP TABLE IF EXISTS `course_materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_materials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `episode_number` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `file_url` varchar(500) NOT NULL,
  `size_mb` decimal(5,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`),
  CONSTRAINT `course_materials_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_materials`
--

LOCK TABLES `course_materials` WRITE;
/*!40000 ALTER TABLE `course_materials` DISABLE KEYS */;
/*!40000 ALTER TABLE `course_materials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `course_subcategories`
--

DROP TABLE IF EXISTS `course_subcategories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `course_subcategories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `course_subcategories_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `course_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `course_subcategories`
--

LOCK TABLES `course_subcategories` WRITE;
/*!40000 ALTER TABLE `course_subcategories` DISABLE KEYS */;
/*!40000 ALTER TABLE `course_subcategories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_code` varchar(50) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) NOT NULL,
  `sub_category` varchar(100) DEFAULT '',
  `grade_level` varchar(50) DEFAULT 'ทั้งหมด',
  `course_month` varchar(50) DEFAULT NULL,
  `instructor` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `image_url` varchar(255) DEFAULT NULL,
  `is_published` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `courses`
--

LOCK TABLES `courses` WRITE;
/*!40000 ALTER TABLE `courses` DISABLE KEYS */;
INSERT INTO `courses` VALUES (1,NULL,'คอร์สอังกฤษเข้มข้น สำหรับ ป.4',NULL,'อังกฤษ','','ป.4',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(2,NULL,'คอร์สอังกฤษเข้มข้น สำหรับ ป.5',NULL,'อังกฤษ','','ป.5',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(3,NULL,'คอร์สอังกฤษเข้มข้น สำหรับ ป.6',NULL,'อังกฤษ','','ป.6',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(4,NULL,'คอร์สอังกฤษเข้มข้น สำหรับ ม.1',NULL,'อังกฤษ','','ม.1',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(5,NULL,'คอร์สอังกฤษเข้มข้น สำหรับ ม.2',NULL,'อังกฤษ','','ม.2',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(6,NULL,'คอร์สอังกฤษเข้มข้น สำหรับ ม.3',NULL,'อังกฤษ','','ม.3',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(7,NULL,'คอร์สอังกฤษเข้มข้น สำหรับ ม.4',NULL,'อังกฤษ','','ม.4',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(8,NULL,'คอร์สอังกฤษเข้มข้น สำหรับ ม.5',NULL,'อังกฤษ','','ม.5',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(9,NULL,'คอร์สอังกฤษเข้มข้น สำหรับ ม.6',NULL,'อังกฤษ','','ม.6',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(10,NULL,'คอร์สคณิตเข้มข้น สำหรับ ป.4',NULL,'คณิต','','ป.4',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(11,NULL,'คอร์สคณิตเข้มข้น สำหรับ ป.5',NULL,'คณิต','','ป.5',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(12,NULL,'คอร์สคณิตเข้มข้น สำหรับ ป.6',NULL,'คณิต','','ป.6',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(13,NULL,'คอร์สคณิตเข้มข้น สำหรับ ม.1',NULL,'คณิต','','ม.1',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(14,NULL,'คอร์สคณิตเข้มข้น สำหรับ ม.2',NULL,'คณิต','','ม.2',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(15,NULL,'คอร์สคณิตเข้มข้น สำหรับ ม.3',NULL,'คณิต','','ม.3',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(16,NULL,'คอร์สคณิตเข้มข้น สำหรับ ม.4',NULL,'คณิต','','ม.4',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(17,NULL,'คอร์สคณิตเข้มข้น สำหรับ ม.5',NULL,'คณิต','','ม.5',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(18,NULL,'คอร์สคณิตเข้มข้น สำหรับ ม.6',NULL,'คณิต','','ม.6',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(19,NULL,'คอร์สวิทย์เข้มข้น สำหรับ ป.4',NULL,'วิทย์','','ป.4',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(20,NULL,'คอร์สวิทย์เข้มข้น สำหรับ ป.5',NULL,'วิทย์','','ป.5',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(21,NULL,'คอร์สวิทย์เข้มข้น สำหรับ ป.6',NULL,'วิทย์','','ป.6',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(22,NULL,'คอร์สวิทย์เข้มข้น สำหรับ ม.1',NULL,'วิทย์','','ม.1',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(23,NULL,'คอร์สวิทย์เข้มข้น สำหรับ ม.2',NULL,'วิทย์','','ม.2',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(24,NULL,'คอร์สวิทย์เข้มข้น สำหรับ ม.3',NULL,'วิทย์','','ม.3',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(25,NULL,'คอร์สวิทย์เข้มข้น สำหรับ ม.4',NULL,'วิทย์','','ม.4',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(26,NULL,'คอร์สวิทย์เข้มข้น สำหรับ ม.5',NULL,'วิทย์','','ม.5',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(27,NULL,'คอร์สวิทย์เข้มข้น สำหรับ ม.6',NULL,'วิทย์','','ม.6',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(28,NULL,'คอร์สไทยเข้มข้น สำหรับ ป.4',NULL,'ไทย','','ป.4',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(29,NULL,'คอร์สไทยเข้มข้น สำหรับ ป.5',NULL,'ไทย','','ป.5',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(30,NULL,'คอร์สไทยเข้มข้น สำหรับ ป.6',NULL,'ไทย','','ป.6',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(31,NULL,'คอร์สไทยเข้มข้น สำหรับ ม.1',NULL,'ไทย','','ม.1',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(32,NULL,'คอร์สไทยเข้มข้น สำหรับ ม.2',NULL,'ไทย','','ม.2',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(33,NULL,'คอร์สไทยเข้มข้น สำหรับ ม.3',NULL,'ไทย','','ม.3',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(34,NULL,'คอร์สไทยเข้มข้น สำหรับ ม.4',NULL,'ไทย','','ม.4',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(35,NULL,'คอร์สไทยเข้มข้น สำหรับ ม.5',NULL,'ไทย','','ม.5',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(36,NULL,'คอร์สไทยเข้มข้น สำหรับ ม.6',NULL,'ไทย','','ม.6',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(37,NULL,'คอร์สสังคมเข้มข้น สำหรับ ป.4',NULL,'สังคม','','ป.4',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(38,NULL,'คอร์สสังคมเข้มข้น สำหรับ ป.5',NULL,'สังคม','','ป.5',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(39,NULL,'คอร์สสังคมเข้มข้น สำหรับ ป.6',NULL,'สังคม','','ป.6',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(40,NULL,'คอร์สสังคมเข้มข้น สำหรับ ม.1',NULL,'สังคม','','ม.1',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(41,NULL,'คอร์สสังคมเข้มข้น สำหรับ ม.2',NULL,'สังคม','','ม.2',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(42,NULL,'คอร์สสังคมเข้มข้น สำหรับ ม.3',NULL,'สังคม','','ม.3',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(43,NULL,'คอร์สสังคมเข้มข้น สำหรับ ม.4',NULL,'สังคม','','ม.4',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(44,NULL,'คอร์สสังคมเข้มข้น สำหรับ ม.5',NULL,'สังคม','','ม.5',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44'),(45,NULL,'คอร์สสังคมเข้มข้น สำหรับ ม.6',NULL,'สังคม','','ม.6',NULL,'อ.ผู้เชี่ยวชาญ',1500.00,NULL,1,'2026-07-22 07:27:44');
/*!40000 ALTER TABLE `courses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `daily_challenges`
--

DROP TABLE IF EXISTS `daily_challenges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `daily_challenges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `challenge_date` date NOT NULL,
  `challenge_type` enum('flashcard','quiz','reading','game') NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `challenge_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`challenge_data`)),
  `xp_reward` int(11) DEFAULT 15,
  PRIMARY KEY (`id`),
  UNIQUE KEY `challenge_date` (`challenge_date`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `daily_challenges`
--

LOCK TABLES `daily_challenges` WRITE;
/*!40000 ALTER TABLE `daily_challenges` DISABLE KEYS */;
INSERT INTO `daily_challenges` VALUES (1,'2026-07-21','flashcard','🃏 ท่องศัพท์ 10 คำ','ทบทวนศัพท์วันนี้ ท่องให้ครบ 10 คำ!',NULL,15),(2,'2026-07-22','reading','📖 อ่านเรื่องสั้น 1 เรื่อง','อ่านเรื่องสั้นแล้วตอบคำถามให้ครบ!',NULL,15);
/*!40000 ALTER TABLE `daily_challenges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exam_permissions`
--

DROP TABLE IF EXISTS `exam_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exam_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `granted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_exam` (`user_id`,`exam_id`),
  KEY `exam_id` (`exam_id`),
  CONSTRAINT `exam_permissions_ibfk_1` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_permissions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exam_permissions`
--

LOCK TABLES `exam_permissions` WRITE;
/*!40000 ALTER TABLE `exam_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `exam_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exam_questions`
--

DROP TABLE IF EXISTS `exam_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exam_questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_id` int(11) NOT NULL,
  `passage_text` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `question_text` text NOT NULL,
  `choice_1` varchar(255) NOT NULL,
  `choice_2` varchar(255) NOT NULL,
  `choice_3` varchar(255) NOT NULL,
  `choice_4` varchar(255) NOT NULL,
  `correct_answer` varchar(255) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `exam_id` (`exam_id`),
  CONSTRAINT `exam_questions_ibfk_1` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exam_questions`
--

LOCK TABLES `exam_questions` WRITE;
/*!40000 ALTER TABLE `exam_questions` DISABLE KEYS */;
INSERT INTO `exam_questions` VALUES (1,4,NULL,NULL,'What is the past tense of \"go\"?','went','gone','going','goes','went',1,'2026-07-22 10:22:06'),(2,4,NULL,NULL,'Which word is a noun?','cat','quickly','run','blue','cat',2,'2026-07-22 10:22:06'),(4,1,'Test passage','','Q1','A1','B1','C1','D1','0',0,'2026-07-23 01:52:44'),(5,1,'Test passage','','Q1','A1','B1','C1','D1','0',0,'2026-07-23 01:52:49');
/*!40000 ALTER TABLE `exam_questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exam_results`
--

DROP TABLE IF EXISTS `exam_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exam_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `total` int(11) NOT NULL,
  `percentage` float NOT NULL,
  `time_spent` int(11) DEFAULT 0 COMMENT 'seconds',
  `answers_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`answers_json`)),
  `completed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `exam_id` (`exam_id`),
  CONSTRAINT `exam_results_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_results_ibfk_2` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exam_results`
--

LOCK TABLES `exam_results` WRITE;
/*!40000 ALTER TABLE `exam_results` DISABLE KEYS */;
/*!40000 ALTER TABLE `exam_results` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exams`
--

DROP TABLE IF EXISTS `exams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `unit` varchar(255) DEFAULT 'Unit 1',
  `lesson_id` int(11) DEFAULT NULL,
  `level` enum('beginner','intermediate','advanced') NOT NULL,
  `total_questions` int(11) DEFAULT 20,
  `time_minutes` int(11) DEFAULT 30,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `subject` varchar(50) DEFAULT 'อังกฤษ',
  `access_mode` enum('public','restricted','locked') DEFAULT 'restricted',
  PRIMARY KEY (`id`),
  KEY `lesson_id` (`lesson_id`),
  CONSTRAINT `exams_ibfk_1` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exams`
--

LOCK TABLES `exams` WRITE;
/*!40000 ALTER TABLE `exams` DISABLE KEYS */;
INSERT INTO `exams` VALUES (1,'แบบทดสอบ Greetings','Unit 1',1,'beginner',10,15,'2026-07-21 09:45:38','อังกฤษ','restricted'),(2,'แบบทดสอบ Numbers','Unit 1',2,'beginner',10,15,'2026-07-21 09:45:38','อังกฤษ','restricted'),(3,'แบบทดสอบ Present Tense','Unit 1',6,'intermediate',10,20,'2026-07-21 09:45:38','อังกฤษ','restricted'),(4,'ข้อสอบรวม Beginner','Unit 1',NULL,'beginner',20,30,'2026-07-21 09:45:38','อังกฤษ','restricted'),(5,'ข้อสอบรวม Intermediate','Unit 1',NULL,'intermediate',20,30,'2026-07-21 09:45:38','อังกฤษ','restricted'),(6,'A-LEVEL ENG ปี 66','Unit 1',NULL,'advanced',80,30,'2026-07-22 17:45:20','English','restricted'),(7,'A-Level ปี 2567 (ล่าสุด)','Unit 1',NULL,'advanced',80,30,'2026-07-22 17:45:20','English','restricted'),(8,'A-level ภาษาอังกฤษ ปี 2568','Unit 1',NULL,'advanced',80,30,'2026-07-22 17:45:20','English','restricted');
/*!40000 ALTER TABLE `exams` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `flashcard_progress`
--

DROP TABLE IF EXISTS `flashcard_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `flashcard_progress` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `vocabulary_id` int(11) NOT NULL,
  `ease_factor` float DEFAULT 2.5,
  `interval_days` int(11) DEFAULT 0,
  `repetitions` int(11) DEFAULT 0,
  `next_review` date DEFAULT NULL,
  `last_reviewed` datetime DEFAULT NULL,
  `times_correct` int(11) DEFAULT 0,
  `times_wrong` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_vocab` (`user_id`,`vocabulary_id`),
  KEY `vocabulary_id` (`vocabulary_id`),
  CONSTRAINT `flashcard_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `flashcard_progress_ibfk_2` FOREIGN KEY (`vocabulary_id`) REFERENCES `vocabulary` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `flashcard_progress`
--

LOCK TABLES `flashcard_progress` WRITE;
/*!40000 ALTER TABLE `flashcard_progress` DISABLE KEYS */;
/*!40000 ALTER TABLE `flashcard_progress` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `game_fill_blanks`
--

DROP TABLE IF EXISTS `game_fill_blanks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `game_fill_blanks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question_text` text NOT NULL,
  `correct_answer` varchar(255) NOT NULL,
  `choice_1` varchar(255) NOT NULL,
  `choice_2` varchar(255) NOT NULL,
  `choice_3` varchar(255) NOT NULL,
  `choice_4` varchar(255) NOT NULL,
  `subject` varchar(50) DEFAULT 'อังกฤษ',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `game_fill_blanks`
--

LOCK TABLES `game_fill_blanks` WRITE;
/*!40000 ALTER TABLE `game_fill_blanks` DISABLE KEYS */;
INSERT INTO `game_fill_blanks` VALUES (1,'I ___ a student.','am','am','is','are','was','อังกฤษ'),(2,'She ___ to school every day.','goes','go','goes','going','went','อังกฤษ'),(3,'They ___ playing football.','are','is','am','are','was','อังกฤษ'),(4,'He ___ not like coffee.','does','do','does','is','was','อังกฤษ'),(5,'We ___ to the park yesterday.','went','go','goes','went','going','อังกฤษ'),(6,'My mother ___ very kind.','is','am','is','are','be','อังกฤษ'),(7,'The dog ___ in the garden.','is','am','is','are','were','อังกฤษ'),(8,'I ___ English every day.','study','study','studies','studying','studied','อังกฤษ');
/*!40000 ALTER TABLE `game_fill_blanks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `game_scores`
--

DROP TABLE IF EXISTS `game_scores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `game_scores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `game_type` enum('match_pairs','sentence_order','fill_blank') NOT NULL,
  `score` int(11) NOT NULL,
  `max_score` int(11) DEFAULT 100,
  `time_spent` int(11) DEFAULT 0,
  `played_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `game_scores_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `game_scores`
--

LOCK TABLES `game_scores` WRITE;
/*!40000 ALTER TABLE `game_scores` DISABLE KEYS */;
/*!40000 ALTER TABLE `game_scores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `game_sentences`
--

DROP TABLE IF EXISTS `game_sentences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `game_sentences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sentence_en` text NOT NULL,
  `sentence_th` text NOT NULL,
  `subject` varchar(50) DEFAULT 'อังกฤษ',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `game_sentences`
--

LOCK TABLES `game_sentences` WRITE;
/*!40000 ALTER TABLE `game_sentences` DISABLE KEYS */;
INSERT INTO `game_sentences` VALUES (1,'I go to school every day','ฉันไปโรงเรียนทุกวัน','อังกฤษ'),(2,'She likes to eat ice cream','เธอชอบกินไอศกรีม','อังกฤษ'),(3,'We play football in the park','เราเล่นฟุตบอลในสวน','อังกฤษ'),(4,'He is a good student','เขาเป็นนักเรียนที่ดี','อังกฤษ'),(5,'My mother cooks delicious food','แม่ทำอาหารอร่อย','อังกฤษ'),(6,'The cat is on the table','แมวอยู่บนโต๊ะ','อังกฤษ'),(7,'They are playing in the garden','พวกเขากำลังเล่นในสวน','อังกฤษ'),(8,'I have two brothers and one sister','ฉันมีพี่ชาย 2 คน และน้องสาว 1 คน','อังกฤษ');
/*!40000 ALTER TABLE `game_sentences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lesson_progress`
--

DROP TABLE IF EXISTS `lesson_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lesson_progress` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `lesson_id` int(11) NOT NULL,
  `completed` tinyint(1) DEFAULT 0,
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_lesson` (`user_id`,`lesson_id`),
  KEY `lesson_id` (`lesson_id`),
  CONSTRAINT `lesson_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lesson_progress_ibfk_2` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lesson_progress`
--

LOCK TABLES `lesson_progress` WRITE;
/*!40000 ALTER TABLE `lesson_progress` DISABLE KEYS */;
/*!40000 ALTER TABLE `lesson_progress` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lessons`
--

DROP TABLE IF EXISTS `lessons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lessons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `level` enum('beginner','intermediate','advanced') NOT NULL,
  `description` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `subject` varchar(50) DEFAULT 'อังกฤษ',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lessons`
--

LOCK TABLES `lessons` WRITE;
/*!40000 ALTER TABLE `lessons` DISABLE KEYS */;
INSERT INTO `lessons` VALUES (1,'Greetings & Self-Introduction','beginner','Hello, My name is..., How are you?','<h3>🎯 วัตถุประสงค์</h3>\n<p>เรียนรู้การทักทายและแนะนำตัวเป็นภาษาอังกฤษ</p>\n<h3>📖 Key Phrases</h3>\n<ul>\n<li><strong>Hello / Hi</strong> — สวัสดี</li>\n<li><strong>Good morning / afternoon / evening</strong> — สวัสดีตอนเช้า/บ่าย/เย็น</li>\n<li><strong>My name is...</strong> — ฉันชื่อ...</li>\n<li><strong>What is your name?</strong> — คุณชื่ออะไร?</li>\n<li><strong>Nice to meet you</strong> — ยินดีที่ได้รู้จัก</li>\n<li><strong>How are you?</strong> — สบายดีไหม?</li>\n<li><strong>I am fine, thank you.</strong> — ฉันสบายดี ขอบคุณ</li>\n</ul>\n<h3>💬 Dialogue</h3>\n<div class=\"dialogue-box\">\n<p><strong>A:</strong> Hello! My name is Somchai. What\'s your name?</p>\n<p><strong>B:</strong> Hi! I\'m Suda. Nice to meet you!</p>\n<p><strong>A:</strong> Nice to meet you too! How are you?</p>\n<p><strong>B:</strong> I\'m fine, thank you. And you?</p>\n<p><strong>A:</strong> I\'m great! Where are you from?</p>\n<p><strong>B:</strong> I\'m from Thailand.</p>\n</div>\n<h3>📝 Tips</h3>\n<p>เวลาทักทายให้ยิ้มและสบตา จะทำให้สนทนาเป็นธรรมชาติมากขึ้น!</p>',1,'2026-07-21 09:45:38','อังกฤษ'),(2,'Numbers & Counting','beginner','One, two, three... to one hundred','<h3>🎯 วัตถุประสงค์</h3>\n<p>เรียนรู้การนับเลข 1–100 เป็นภาษาอังกฤษ</p>\n<h3>📖 Numbers 1-20</h3>\n<div class=\"number-grid\">1 = one, 2 = two, 3 = three, 4 = four, 5 = five, 6 = six, 7 = seven, 8 = eight, 9 = nine, 10 = ten, 11 = eleven, 12 = twelve, 13 = thirteen, 14 = fourteen, 15 = fifteen, 16 = sixteen, 17 = seventeen, 18 = eighteen, 19 = nineteen, 20 = twenty</div>\n<h3>📖 Tens</h3>\n<p>30 = thirty, 40 = forty, 50 = fifty, 60 = sixty, 70 = seventy, 80 = eighty, 90 = ninety, 100 = one hundred</p>',2,'2026-07-21 09:45:38','อังกฤษ'),(3,'Days & Months','beginner','Monday, January, today, tomorrow','<h3>🎯 วัตถุประสงค์</h3><p>เรียนรู้วันในสัปดาห์และเดือนต่างๆ</p>\n<h3>📅 Days of the Week</h3>\n<p>Monday (จันทร์), Tuesday (อังคาร), Wednesday (พุธ), Thursday (พฤหัสบดี), Friday (ศุกร์), Saturday (เสาร์), Sunday (อาทิตย์)</p>\n<h3>📅 Months of the Year</h3>\n<p>January, February, March, April, May, June, July, August, September, October, November, December</p>',3,'2026-07-21 09:45:38','อังกฤษ'),(4,'Family Members','beginner','Father, mother, brother, sister','<h3>🎯 วัตถุประสงค์</h3><p>เรียนรู้คำศัพท์เกี่ยวกับครอบครัว</p>\n<h3>👨‍👩‍👧‍👦 Family</h3>\n<p>Father (พ่อ), Mother (แม่), Brother (พี่/น้องชาย), Sister (พี่/น้องสาว), Grandfather (ปู่/ตา), Grandmother (ย่า/ยาย), Uncle (ลุง/อา), Aunt (ป้า/น้า)</p>',4,'2026-07-21 09:45:38','อังกฤษ'),(5,'Colors & Shapes','beginner','Red, blue, circle, square','<h3>🎯 วัตถุประสงค์</h3><p>เรียนรู้สีและรูปทรงต่างๆ</p>\n<h3>🎨 Colors</h3>\n<p>Red (แดง), Blue (น้ำเงิน), Green (เขียว), Yellow (เหลือง), Orange (ส้ม), Purple (ม่วง), Pink (ชมพู), Black (ดำ), White (ขาว), Brown (น้ำตาล)</p>\n<h3>🔷 Shapes</h3>\n<p>Circle (วงกลม), Square (สี่เหลี่ยมจัตุรัส), Triangle (สามเหลี่ยม), Rectangle (สี่เหลี่ยมผืนผ้า), Star (ดาว), Heart (หัวใจ)</p>',5,'2026-07-21 09:45:38','อังกฤษ'),(6,'Present Simple Tense','intermediate','I go, She goes, Do you...?','<h3>🎯 วัตถุประสงค์</h3><p>เข้าใจหลักการใช้ Present Simple Tense</p>\n<h3>📖 Structure</h3>\n<p><strong>Positive:</strong> Subject + V1 (เติม s/es สำหรับ he, she, it)</p>\n<p><strong>Negative:</strong> Subject + do/does + not + V1</p>\n<p><strong>Question:</strong> Do/Does + Subject + V1?</p>\n<h3>📝 Examples</h3>\n<p>I <strong>go</strong> to school every day.</p>\n<p>She <strong>goes</strong> to school every day.</p>\n<p><strong>Do</strong> you like ice cream?</p>\n<p>He <strong>does not</strong> (doesn\'t) play football.</p>',6,'2026-07-21 09:45:38','อังกฤษ'),(7,'Past Simple Tense','intermediate','I went, She visited, Did you...?','<h3>🎯 วัตถุประสงค์</h3><p>เข้าใจหลักการใช้ Past Simple Tense</p>\n<h3>📖 Structure</h3>\n<p><strong>Positive:</strong> Subject + V2</p>\n<p><strong>Negative:</strong> Subject + did not + V1</p>\n<p><strong>Question:</strong> Did + Subject + V1?</p>\n<h3>📝 Examples</h3>\n<p>I <strong>went</strong> to the park yesterday.</p>\n<p>She <strong>visited</strong> her grandmother last week.</p>\n<p><strong>Did</strong> you eat breakfast this morning?</p>',7,'2026-07-21 09:45:38','อังกฤษ'),(8,'Future Tense','intermediate','Will, going to, shall','<h3>🎯 วัตถุประสงค์</h3><p>เข้าใจหลักการใช้ Future Tense</p>\n<h3>📖 Will</h3>\n<p>Subject + will + V1</p>\n<p>I <strong>will go</strong> to the beach tomorrow.</p>\n<h3>📖 Going to</h3>\n<p>Subject + am/is/are + going to + V1</p>\n<p>She <strong>is going to</strong> study tonight.</p>',8,'2026-07-21 09:45:38','อังกฤษ'),(9,'Comparative & Superlative','intermediate','bigger, the biggest, more beautiful','<h3>🎯 วัตถุประสงค์</h3><p>เรียนรู้การเปรียบเทียบ</p>\n<h3>📖 Comparative (-er / more)</h3>\n<p>big → bigger, tall → taller, beautiful → more beautiful</p>\n<h3>📖 Superlative (-est / most)</h3>\n<p>big → the biggest, tall → the tallest, beautiful → the most beautiful</p>',9,'2026-07-21 09:45:38','อังกฤษ'),(10,'Conditionals','advanced','If I were..., If I had...','<h3>🎯 วัตถุประสงค์</h3><p>เรียนรู้ประโยคเงื่อนไข (If clauses)</p>\n<h3>📖 Zero Conditional</h3><p>If + present simple, present simple (ความจริงทั่วไป)</p>\n<h3>📖 First Conditional</h3><p>If + present simple, will + V1 (เป็นไปได้ในอนาคต)</p>\n<h3>📖 Second Conditional</h3><p>If + past simple, would + V1 (สมมติไม่จริง)</p>\n<h3>📖 Third Conditional</h3><p>If + past perfect, would have + V3 (เสียดายอดีต)</p>',10,'2026-07-21 09:45:38','อังกฤษ'),(11,'Passive Voice','advanced','The cake was eaten, It is being built','<h3>🎯 วัตถุประสงค์</h3><p>เรียนรู้ Passive Voice (ประโยคถูกกระทำ)</p>\n<h3>📖 Structure</h3>\n<p>Subject + be + V3 (Past Participle)</p>\n<h3>📝 Examples</h3>\n<p>Active: The dog <strong>bites</strong> the man.</p>\n<p>Passive: The man <strong>is bitten</strong> by the dog.</p>',11,'2026-07-21 09:45:38','อังกฤษ'),(12,'Reported Speech','advanced','He said that..., She told me...','<h3>🎯 วัตถุประสงค์</h3><p>เรียนรู้การเปลี่ยนคำพูดตรงเป็นคำพูดอ้อม</p>\n<h3>📖 Rules</h3>\n<p>Direct: \"I <strong>am</strong> happy.\"</p>\n<p>Reported: He said he <strong>was</strong> happy.</p>\n<p>เลื่อน tense ลงหนึ่งขั้น เช่น am→was, will→would, can→could</p>',12,'2026-07-21 09:45:38','อังกฤษ');
/*!40000 ALTER TABLE `lessons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `slip_image` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `course_id` (`course_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,8,1,1500.00,'linguamax/uploads/slips/slip_1784708527_502.png','approved','2026-07-22 08:22:07');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_methods`
--

DROP TABLE IF EXISTS `payment_methods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_methods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bank_name` varchar(100) NOT NULL,
  `account_name` varchar(255) NOT NULL,
  `account_number` varchar(100) NOT NULL,
  `qr_code_image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_methods`
--

LOCK TABLES `payment_methods` WRITE;
/*!40000 ALTER TABLE `payment_methods` DISABLE KEYS */;
INSERT INTO `payment_methods` VALUES (1,'Kasikorn Bank (KBank)','Test','123-4-56789-0','admin/uploads/payments/qr_1784708346_501.png',1,'2026-07-22 08:06:06');
/*!40000 ALTER TABLE `payment_methods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `questions`
--

DROP TABLE IF EXISTS `questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_id` int(11) DEFAULT NULL,
  `question_text` text NOT NULL,
  `choice_a` varchar(500) NOT NULL,
  `choice_b` varchar(500) NOT NULL,
  `choice_c` varchar(500) NOT NULL,
  `choice_d` varchar(500) NOT NULL,
  `choice_e` varchar(500) DEFAULT NULL,
  `correct_answer` tinyint(4) NOT NULL COMMENT '0=A,1=B,2=C,3=D,4=E',
  `level` enum('beginner','intermediate','advanced') DEFAULT 'beginner',
  PRIMARY KEY (`id`),
  KEY `exam_id` (`exam_id`),
  CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `questions`
--

LOCK TABLES `questions` WRITE;
/*!40000 ALTER TABLE `questions` DISABLE KEYS */;
INSERT INTO `questions` VALUES (1,1,'What is the correct greeting for the morning?','Good night','Good morning','Good bye','Good luck','Good grief',1,'beginner'),(2,1,'\"How are you?\" — ตอบว่าอะไร?','I am 10 years old.','My name is Tom.','I\'m fine, thank you.','I live in Bangkok.','I like ice cream.',2,'beginner'),(3,1,'What does \"Nice to meet you\" mean?','ลาก่อน','ขอโทษ','ยินดีที่ได้รู้จัก','ขอบคุณ','ไม่เป็นไร',2,'beginner'),(4,1,'\"My name _____ Suda.\"','am','is','are','was','be',1,'beginner'),(5,1,'Which word means \"สวัสดี\"?','Sorry','Thank you','Hello','Goodbye','Please',2,'beginner'),(6,1,'\"Good _____\" — กล่าวตอนเย็น','morning','afternoon','evening','night','day',2,'beginner'),(7,1,'\"I _____ a student.\"','is','am','are','was','be',1,'beginner'),(8,1,'\"_____ is your name?\" — \"My name is Tom.\"','How','Where','What','When','Why',2,'beginner'),(9,1,'\"See you _____\" means \"แล้วพบกันพรุ่งนี้\"','yesterday','today','tomorrow','now','later',2,'beginner'),(10,1,'\"Thank you\" ตอบว่า _____','Sorry','Hello','You\'re welcome','Goodbye','Excuse me',2,'beginner'),(11,2,'How do you say \"5\" in English?','Three','Five','Seven','Nine',NULL,1,'beginner'),(12,2,'What number comes after twelve?','Eleven','Thirteen','Fourteen','Ten',NULL,1,'beginner'),(13,2,'How do you spell 20?','Twanty','Twenty','Twentie','Twenti',NULL,1,'beginner'),(14,2,'What is 10 + 5?','Thirteen','Fourteen','Fifteen','Sixteen',NULL,2,'beginner'),(15,2,'\"One hundred\" equals what number?','10','50','100','1000',NULL,2,'beginner'),(16,2,'How many days are in a week?','Five','Six','Seven','Eight',NULL,2,'beginner'),(17,2,'What is the number after \"nineteen\"?','Eighteen','Twenty','Twenty-one','Seventeen',NULL,1,'beginner'),(18,2,'\"Fifty\" means _____','15','50','500','5',NULL,1,'beginner'),(19,2,'How do you say \"1000\"?','One hundred','One thousand','Ten hundred','One million',NULL,1,'beginner'),(20,2,'What is 7 + 3?','Nine','Ten','Eleven','Eight',NULL,1,'beginner'),(21,3,'She _____ to school every day.','go','goes','going','went','gone',1,'intermediate'),(22,3,'They _____ playing football now.','is','am','are','was','be',2,'intermediate'),(23,3,'I _____ breakfast at 7 AM.','has','have','having','had','haves',1,'intermediate'),(24,3,'_____ you like ice cream?','Does','Do','Is','Are','Was',1,'intermediate'),(25,3,'He _____ not speak Japanese.','do','does','is','are','have',1,'intermediate'),(26,3,'We _____ to the park yesterday.','go','goes','going','went','gone',3,'intermediate'),(27,3,'The cat is _____ the table.','in','on','at','under','by',1,'intermediate'),(28,3,'She is _____ than her sister.','tall','taller','tallest','more tall','most tall',1,'intermediate'),(29,3,'I will _____ you tomorrow.','see','sees','seeing','saw','seen',0,'intermediate'),(30,3,'This is _____ best movie I have ever seen.','a','an','the','-','some',2,'intermediate'),(31,4,'What color is the sky?','Red','Blue','Green','Yellow',NULL,1,'beginner'),(32,4,'\"Dog\" in Thai is _____','แมว','สุนัข','นก','ปลา',NULL,1,'beginner'),(33,4,'Which day comes after Monday?','Wednesday','Tuesday','Sunday','Thursday',NULL,1,'beginner'),(34,4,'\"Mother\" means _____','พ่อ','แม่','พี่','น้อง',NULL,1,'beginner'),(35,4,'What shape has 3 sides?','Square','Circle','Triangle','Rectangle',NULL,2,'beginner'),(36,4,'I _____ happy today.','is','am','are','was',NULL,1,'beginner'),(37,4,'\"Apple\" is a _____','animal','fruit','color','day',NULL,1,'beginner'),(38,4,'How many months in a year?','Ten','Eleven','Twelve','Thirteen',NULL,2,'beginner'),(39,4,'\"Elephant\" in Thai is _____','แมว','สุนัข','ช้าง','ม้า',NULL,2,'beginner'),(40,4,'Which word means \"น้ำ\"?','Fire','Water','Air','Earth',NULL,1,'beginner'),(41,4,'What is the opposite of \"happy\"?','Angry','Sad','Tired','Excited',NULL,1,'beginner'),(42,4,'\"School\" means _____','บ้าน','ตลาด','โรงเรียน','โรงพยาบาล',NULL,2,'beginner'),(43,4,'Which is NOT a color?','Red','Blue','Dog','Green',NULL,2,'beginner'),(44,4,'\"Brother\" means _____','พี่สาว','พ่อ','น้องชาย/พี่ชาย','ลุง',NULL,2,'beginner'),(45,4,'I eat _____ every day.','rice','school','house','park',NULL,0,'beginner'),(46,4,'\"Library\" is a place for _____','eating','sleeping','reading','swimming',NULL,2,'beginner'),(47,4,'What color is a banana?','Red','Blue','Yellow','Purple',NULL,2,'beginner'),(48,4,'\"Fish\" lives in _____','tree','water','sky','house',NULL,1,'beginner'),(49,4,'What day is after Friday?','Thursday','Saturday','Sunday','Monday',NULL,1,'beginner'),(50,4,'\"Goodbye\" means _____','สวัสดี','ขอบคุณ','ลาก่อน','ขอโทษ',NULL,2,'beginner'),(51,5,'She _____ to school every day.','go','goes','going','went',NULL,1,'intermediate'),(52,5,'I _____ breakfast at 7 AM.','has','have','having','had',NULL,1,'intermediate'),(53,5,'_____ you like ice cream?','Does','Do','Is','Are',NULL,1,'intermediate'),(54,5,'We _____ to the park yesterday.','go','goes','going','went',NULL,3,'intermediate'),(55,5,'She is _____ than her sister.','tall','taller','tallest','more tall',NULL,1,'intermediate'),(56,5,'I will _____ you tomorrow.','see','sees','seeing','saw',NULL,0,'intermediate'),(57,5,'This is _____ best movie ever.','a','an','the','-',NULL,2,'intermediate'),(58,5,'He _____ not speak Japanese.','do','does','is','are',NULL,1,'intermediate'),(59,5,'They _____ playing football now.','is','am','are','was',NULL,2,'intermediate'),(60,5,'I _____ never been to Japan.','has','have','had','having',NULL,1,'intermediate'),(61,5,'The cat is _____ the table.','in','on','at','under',NULL,1,'intermediate'),(62,5,'If it rains, I _____ stay home.','will','would','can','may',NULL,0,'intermediate'),(63,5,'She asked me _____ I was from.','what','where','when','who',NULL,1,'intermediate'),(64,5,'He runs _____ than his brother.','fast','faster','fastest','more fast',NULL,1,'intermediate'),(65,5,'I enjoy _____ English.','learn','learning','learned','learns',NULL,1,'intermediate'),(66,5,'The movie was _____ interesting.','much','many','very','lot',NULL,2,'intermediate'),(67,5,'She _____ here since 2020.','is','was','has been','had been',NULL,2,'intermediate'),(68,5,'Would you mind _____ the window?','open','opening','opened','opens',NULL,1,'intermediate'),(69,5,'I wish I _____ fly.','can','could','will','am',NULL,1,'intermediate'),(70,5,'Neither Tom _____ Jerry is here.','or','and','nor','but',NULL,2,'intermediate');
/*!40000 ALTER TABLE `questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reading_passages`
--

DROP TABLE IF EXISTS `reading_passages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reading_passages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `title_th` varchar(255) DEFAULT NULL,
  `content` longtext NOT NULL,
  `level` enum('beginner','intermediate','advanced') NOT NULL,
  `word_count` int(11) DEFAULT 0,
  `category` varchar(50) DEFAULT 'story',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `subject` varchar(50) DEFAULT 'อังกฤษ',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reading_passages`
--

LOCK TABLES `reading_passages` WRITE;
/*!40000 ALTER TABLE `reading_passages` DISABLE KEYS */;
INSERT INTO `reading_passages` VALUES (1,'My Pet Dog','สุนัขของฉัน','<p>My name is Ploy. I am ten years old. I have a pet dog. His name is Lucky. Lucky is a small brown dog. He has big brown eyes and a short tail.</p>\n<p>Every morning, I wake up and feed Lucky. He likes to eat rice and chicken. After eating, Lucky likes to play in the garden. He runs very fast!</p>\n<p>In the evening, I take Lucky for a walk. We walk around the park near my house. Lucky is very friendly. He likes to play with other dogs.</p>\n<p>At night, Lucky sleeps next to my bed. He is my best friend. I love Lucky very much!</p>','beginner',95,'story','2026-07-21 09:45:38','อังกฤษ'),(2,'A Day at School','วันหนึ่งที่โรงเรียน','<p>Today is Monday. I go to school at 7:30 in the morning. My school is not far from my house. I walk to school with my friend, Nong.</p>\n<p>At school, we study many subjects. In the morning, we have English class. I like English because the teacher is very kind. She teaches us new words every day.</p>\n<p>At lunchtime, I eat lunch in the cafeteria. I usually eat rice with chicken. Sometimes I eat noodles. After lunch, we play in the playground.</p>\n<p>In the afternoon, we have Math and Science. Math is a little difficult, but I try my best. School finishes at 3:30 PM. I go home and do my homework. Then I play with my friends.</p>','beginner',120,'daily_life','2026-07-21 09:45:38','อังกฤษ'),(3,'The Little Red Hen','แม่ไก่ตัวน้อยสีแดง','<p>Once upon a time, there was a little red hen. She lived on a farm with a dog, a cat, and a duck.</p>\n<p>One day, the little red hen found some wheat seeds. \"Who will help me plant these seeds?\" she asked.</p>\n<p>\"Not I,\" said the dog. \"Not I,\" said the cat. \"Not I,\" said the duck.</p>\n<p>\"Then I will do it myself,\" said the little red hen. And she did.</p>\n<p>The wheat grew tall and golden. \"Who will help me cut the wheat?\" she asked. Again, nobody wanted to help.</p>\n<p>The little red hen cut the wheat, made flour, and baked a delicious bread all by herself.</p>\n<p>\"Who will help me eat this bread?\" she asked. \"I will!\" said the dog, the cat, and the duck.</p>\n<p>\"No,\" said the little red hen. \"I planted the seeds. I cut the wheat. I made the bread. I will eat it myself!\" And she did.</p>\n<p><strong>Moral:</strong> If you want to enjoy the rewards, you must help with the work.</p>','beginner',165,'fable','2026-07-21 09:45:38','อังกฤษ'),(4,'My Summer Vacation','ปิดเทอมของฉัน','<p>Last summer, my family and I went to Phuket for a vacation. We traveled by airplane. It was my first time on a plane, and I was very excited!</p>\n<p>We stayed at a hotel near the beach. Every morning, we woke up early and went swimming in the sea. The water was warm and clear. I could see many colorful fish!</p>\n<p>One day, we took a boat to a small island. There were beautiful coral reefs under the water. My father taught me how to snorkel. It was amazing! I saw starfish, sea urchins, and even a small octopus.</p>\n<p>In the evening, we walked along the beach and watched the sunset. The sky turned orange, pink, and purple. It was the most beautiful thing I had ever seen.</p>\n<p>We also visited a night market. There was delicious seafood everywhere. I tried grilled squid and mango sticky rice. Everything was so yummy!</p>\n<p>The vacation lasted five days, but it felt too short. I want to go back to Phuket again next year!</p>','intermediate',170,'story','2026-07-21 09:45:38','อังกฤษ'),(5,'The Importance of Learning English','ความสำคัญของการเรียนภาษาอังกฤษ','<p>English is one of the most widely spoken languages in the world. It is the official language of over 50 countries and is used as a second language in many more. Learning English can open many doors in your life.</p>\n<p>First, English is the language of technology and the internet. Most websites, apps, and computer programs are written in English. If you understand English, you can access a huge amount of information online.</p>\n<p>Second, English is important for your future career. Many international companies require their employees to speak English. If you can communicate well in English, you will have more job opportunities.</p>\n<p>Third, English helps you understand different cultures. Through English books, movies, and music, you can learn about people from all around the world. This makes you more open-minded and understanding.</p>\n<p>Finally, learning English improves your brain power. Studies show that bilingual people have better memory, problem-solving skills, and multitasking abilities.</p>\n<p>So, keep studying English every day! Even learning a few new words each day will make a big difference over time. Remember, practice makes perfect!</p>','intermediate',175,'educational','2026-07-21 09:45:38','อังกฤษ'),(6,'The Mystery of the Missing Homework','ปริศนาการบ้านหาย','<p>It was a typical Thursday morning when Tom walked into his classroom. He reached into his backpack to take out his homework, but it was gone! He had spent two hours working on it the night before.</p>\n<p>\"Where could it be?\" Tom wondered. He checked every pocket of his bag. He looked under his desk. He even asked his classmates if they had accidentally taken it. Nobody had seen it.</p>\n<p>The teacher, Mrs. Johnson, was not happy. \"Tom, this is the third time this month that you haven\'t submitted your homework,\" she said with a disappointed expression.</p>\n<p>\"But I really did it this time!\" Tom insisted. He felt frustrated because he was telling the truth.</p>\n<p>During lunch break, Tom decided to investigate. He retraced his steps from the morning. First, he had walked from his house to the bus stop. Then he took the bus to school. Finally, he walked from the bus stop to the classroom.</p>\n<p>Suddenly, he remembered something. On the bus, he had taken out his homework to review it one more time. Could he have left it on the bus?</p>\n<p>After school, Tom went to the bus company\'s lost and found office. There, sitting on a shelf, was his homework! He was so relieved.</p>\n<p>The next day, Tom showed his homework to Mrs. Johnson and explained what had happened. She smiled and said, \"I\'m glad you found it, Tom. But from now on, keep your homework safely in your bag!\"</p>','advanced',240,'story','2026-07-21 09:45:38','อังกฤษ');
/*!40000 ALTER TABLE `reading_passages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reading_progress`
--

DROP TABLE IF EXISTS `reading_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reading_progress` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `passage_id` int(11) NOT NULL,
  `score` int(11) DEFAULT 0,
  `total` int(11) DEFAULT 0,
  `completed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `passage_id` (`passage_id`),
  CONSTRAINT `reading_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reading_progress_ibfk_2` FOREIGN KEY (`passage_id`) REFERENCES `reading_passages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reading_progress`
--

LOCK TABLES `reading_progress` WRITE;
/*!40000 ALTER TABLE `reading_progress` DISABLE KEYS */;
/*!40000 ALTER TABLE `reading_progress` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reading_questions`
--

DROP TABLE IF EXISTS `reading_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reading_questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `passage_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `choice_a` varchar(500) NOT NULL,
  `choice_b` varchar(500) NOT NULL,
  `choice_c` varchar(500) NOT NULL,
  `choice_d` varchar(500) NOT NULL,
  `correct_answer` tinyint(4) NOT NULL COMMENT '0=A,1=B,2=C,3=D',
  PRIMARY KEY (`id`),
  KEY `passage_id` (`passage_id`),
  CONSTRAINT `reading_questions_ibfk_1` FOREIGN KEY (`passage_id`) REFERENCES `reading_passages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reading_questions`
--

LOCK TABLES `reading_questions` WRITE;
/*!40000 ALTER TABLE `reading_questions` DISABLE KEYS */;
INSERT INTO `reading_questions` VALUES (1,1,'What is the dog\'s name?','Ploy','Lucky','Buddy','Max',1),(2,1,'What color is Lucky?','White','Black','Brown','Golden',2),(3,1,'What does Lucky like to eat?','Fish and bread','Rice and chicken','Dog food','Fruits',1),(4,1,'Where does Ploy walk Lucky?','At school','In the garden','In the park','On the road',2),(5,1,'Where does Lucky sleep?','In the garden','Next to Ploy\'s bed','On the sofa','In his own room',1),(6,2,'What time does the student go to school?','7:00 AM','7:30 AM','8:00 AM','8:30 AM',1),(7,2,'Which subject does the student like?','Math','Science','English','Art',2),(8,2,'Where does the student eat lunch?','At home','In the classroom','In the cafeteria','In the park',2),(9,2,'What time does school finish?','3:00 PM','3:30 PM','4:00 PM','4:30 PM',1),(10,2,'What does the student do after school?','Go shopping','Do homework and play','Watch TV','Go to sleep',1),(11,3,'Who lived on the farm with the hen?','A cow, a pig, and a horse','A dog, a cat, and a duck','A rabbit, a mouse, and a bird','A sheep, a goat, and a pig',1),(12,3,'What did the hen find?','Corn seeds','Wheat seeds','Rice seeds','Flower seeds',1),(13,3,'Who helped the hen plant the seeds?','The dog','The cat','The duck','Nobody',3),(14,3,'What did the hen bake?','A cake','A pie','Bread','Cookies',2),(15,3,'What is the moral of the story?','Sharing is caring','Help with work to enjoy rewards','Always be kind','Never give up',1),(16,4,'Where did the family go for vacation?','Chiang Mai','Phuket','Hua Hin','Pattaya',1),(17,4,'How did they travel?','By car','By bus','By airplane','By train',2),(18,4,'What did the father teach?','Swimming','Snorkeling','Diving','Fishing',1),(19,4,'What did they eat at the night market?','Pizza and pasta','Grilled squid and mango sticky rice','Hamburgers and fries','Sushi and ramen',1),(20,4,'How long was the vacation?','Three days','Five days','One week','Two weeks',1),(21,5,'How many countries use English as an official language?','Over 30','Over 50','Over 70','Over 100',1),(22,5,'Why is English important for technology?','Because computers are made in England','Because most websites and programs are in English','Because English is easy','Because scientists speak English',1),(23,5,'What does learning English improve?','Physical strength','Brain power','Cooking skills','Dancing ability',1),(24,5,'What is the advice given in the passage?','Study English once a week','Learn a few new words each day','Only watch English movies','Travel to English-speaking countries',1),(25,5,'What makes bilingual people better?','They earn more money','They have better memory and problem-solving','They are taller','They run faster',1),(26,6,'On which day did Tom lose his homework?','Monday','Wednesday','Thursday','Friday',2),(27,6,'How many times had Tom not submitted homework that month?','One','Two','Three','Four',2),(28,6,'Where did Tom leave his homework?','At home','At school','On the bus','At the library',2),(29,6,'How did Tom feel when the teacher scolded him?','Happy','Frustrated','Scared','Bored',1),(30,6,'Where did Tom find his homework?','In his backpack','Under his desk','At the lost and found office','At home',2);
/*!40000 ALTER TABLE `reading_questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text NOT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_settings`
--

LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_activity_log`
--

DROP TABLE IF EXISTS `user_activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `activity_type` enum('lesson','exam','game','pronunciation','reading') NOT NULL,
  `activity_details` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_activity_log`
--

LOCK TABLES `user_activity_log` WRITE;
/*!40000 ALTER TABLE `user_activity_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_activity_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_badges`
--

DROP TABLE IF EXISTS `user_badges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_badges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `badge_id` int(11) NOT NULL,
  `earned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_badge` (`user_id`,`badge_id`),
  KEY `badge_id` (`badge_id`),
  CONSTRAINT `user_badges_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_badges_ibfk_2` FOREIGN KEY (`badge_id`) REFERENCES `badges` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_badges`
--

LOCK TABLES `user_badges` WRITE;
/*!40000 ALTER TABLE `user_badges` DISABLE KEYS */;
INSERT INTO `user_badges` VALUES (18,8,1,'2026-07-22 03:09:33');
/*!40000 ALTER TABLE `user_badges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_daily_progress`
--

DROP TABLE IF EXISTS `user_daily_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_daily_progress` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `challenge_date` date NOT NULL,
  `completed` tinyint(1) DEFAULT 0,
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_date` (`user_id`,`challenge_date`),
  CONSTRAINT `user_daily_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_daily_progress`
--

LOCK TABLES `user_daily_progress` WRITE;
/*!40000 ALTER TABLE `user_daily_progress` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_daily_progress` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_streaks`
--

DROP TABLE IF EXISTS `user_streaks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_streaks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `current_streak` int(11) DEFAULT 0,
  `longest_streak` int(11) DEFAULT 0,
  `last_activity_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_streaks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_streaks`
--

LOCK TABLES `user_streaks` WRITE;
/*!40000 ALTER TABLE `user_streaks` DISABLE KEYS */;
INSERT INTO `user_streaks` VALUES (7,8,1,1,'2026-07-22');
/*!40000 ALTER TABLE `user_streaks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_surveys`
--

DROP TABLE IF EXISTS `user_surveys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_surveys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `answers_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`answers_json`)),
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_surveys_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_surveys`
--

LOCK TABLES `user_surveys` WRITE;
/*!40000 ALTER TABLE `user_surveys` DISABLE KEYS */;
INSERT INTO `user_surveys` VALUES (1,9,'{\"nickname\":\"I\'m Mocky\",\"grade\":\"ป.6\",\"fav\":\"Math\",\"note\":\"Testing quote\'s\"}','2026-07-23 07:40:16'),(2,8,'{\"nickname\":\"test\",\"grade\":\"\\u0e1b.6\",\"fav\":[\"\\u0e04\\u0e13\\u0e34\\u0e15\\u0e28\\u0e32\\u0e2a\\u0e15\\u0e23\\u0e4c\"],\"hard\":[\"\\u0e04\\u0e13\\u0e34\\u0e15\\u0e28\\u0e32\\u0e2a\\u0e15\\u0e23\\u0e4c\"],\"helper\":\"\\u0e27\\u0e34\\u0e14\\u0e35\\u0e42\\u0e2d\\u0e01\\u0e32\\u0e23\\u0e4c\\u0e15\\u0e39\\u0e19\\u0e2a\\u0e2d\\u0e19\\u0e1a\\u0e17\\u0e40\\u0e23\\u0e35\\u0e22\\u0e19\",\"gametype\":[\"\\u0e15\\u0e2d\\u0e1a\\u0e04\\u0e33\\u0e16\\u0e32\\u0e21\\u0e41\\u0e02\\u0e48\\u0e07\\u0e01\\u0e31\\u0e1a\\u0e40\\u0e1e\\u0e37\\u0e48\\u0e2d\\u0e19\"],\"reward\":[\"\\u0e40\\u0e2b\\u0e23\\u0e35\\u0e22\\u0e0d \\/ \\u0e14\\u0e32\\u0e27\\u0e2a\\u0e30\\u0e2a\\u0e21\"],\"play\":\"\\u0e04\\u0e19\\u0e40\\u0e14\\u0e35\\u0e22\\u0e27\\u0e0a\\u0e34\\u0e25\\u0e46\",\"dream\":\"test\",\"note\":\"test\",\"submittedAt\":\"2026-07-23T07:42:11.208Z\"}','2026-07-23 07:42:11');
/*!40000 ALTER TABLE `user_surveys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(10) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `fname` varchar(100) NOT NULL,
  `lname` varchar(100) NOT NULL,
  `nickname` varchar(50) NOT NULL,
  `role` enum('student','admin') DEFAULT 'student',
  `level` enum('beginner','intermediate','advanced') DEFAULT 'beginner',
  `xp` int(11) DEFAULT 0,
  `coins` int(11) DEFAULT 0,
  `avatar_color` varchar(7) DEFAULT '#6C63FF',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `daily_xp_goal` int(11) DEFAULT 50,
  `sound_enabled` tinyint(1) DEFAULT 1,
  `notifications_enabled` tinyint(1) DEFAULT 1,
  `character_id` varchar(50) DEFAULT 'default',
  `profile_pic` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'ADMIN01','admin','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','ครูสมศรี','ใจดี','ครูศรี','admin','advanced',0,0,'#6C63FF','2026-07-21 09:45:38','2026-07-21 09:45:38',50,1,1,'default',NULL),(8,'STD001',NULL,'$2y$10$JTGxy5uRb.PS/r5g4zpFRe6YeBO5QA5xWAdkAqSI4Ia9FoIZK9H4W','test','test','test','student','beginner',50,50,'#8B5CF6','2026-07-21 18:50:35','2026-07-23 07:42:11',50,1,1,'char1',NULL),(9,'MOCK001','mockstudent','pass','Mock','Student','Mocky','student','beginner',0,0,'#6C63FF','2026-07-23 07:40:16','2026-07-23 07:40:16',50,1,1,'default',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vocabulary`
--

DROP TABLE IF EXISTS `vocabulary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vocabulary` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lesson_id` int(11) DEFAULT NULL,
  `word_en` varchar(255) NOT NULL,
  `word_th` varchar(255) NOT NULL,
  `pronunciation` varchar(255) DEFAULT NULL,
  `example_sentence` text DEFAULT NULL,
  `level` enum('beginner','intermediate','advanced') DEFAULT 'beginner',
  `category` varchar(50) DEFAULT 'general',
  `subject` varchar(50) DEFAULT 'อังกฤษ',
  PRIMARY KEY (`id`),
  KEY `lesson_id` (`lesson_id`),
  CONSTRAINT `vocabulary_ibfk_1` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vocabulary`
--

LOCK TABLES `vocabulary` WRITE;
/*!40000 ALTER TABLE `vocabulary` DISABLE KEYS */;
INSERT INTO `vocabulary` VALUES (1,1,'Hello','สวัสดี','/həˈloʊ/','Hello! How are you?','beginner','greetings','อังกฤษ'),(2,1,'Good morning','สวัสดีตอนเช้า','/ɡʊd ˈmɔːrnɪŋ/','Good morning, teacher!','beginner','greetings','อังกฤษ'),(3,1,'Good afternoon','สวัสดีตอนบ่าย','/ɡʊd ˌæftərˈnuːn/','Good afternoon, everyone!','beginner','greetings','อังกฤษ'),(4,1,'Good evening','สวัสดีตอนเย็น','/ɡʊd ˈiːvnɪŋ/','Good evening, sir.','beginner','greetings','อังกฤษ'),(5,1,'Goodbye','ลาก่อน','/ɡʊdˈbaɪ/','Goodbye! See you tomorrow.','beginner','greetings','อังกฤษ'),(6,1,'Thank you','ขอบคุณ','/θæŋk juː/','Thank you very much!','beginner','greetings','อังกฤษ'),(7,1,'Please','ได้โปรด','/pliːz/','Please sit down.','beginner','greetings','อังกฤษ'),(8,1,'Sorry','ขอโทษ','/ˈsɒri/','I\'m sorry for being late.','beginner','greetings','อังกฤษ'),(9,1,'Excuse me','ขอโทษ (ขออนุญาต)','/ɪkˈskjuːz miː/','Excuse me, where is the restroom?','beginner','greetings','อังกฤษ'),(10,1,'Nice to meet you','ยินดีที่ได้รู้จัก','/naɪs tuː miːt juː/','Nice to meet you, Suda!','beginner','greetings','อังกฤษ'),(11,2,'One','หนึ่ง','/wʌn/','I have one dog.','beginner','numbers','อังกฤษ'),(12,2,'Two','สอง','/tuː/','She has two cats.','beginner','numbers','อังกฤษ'),(13,2,'Three','สาม','/θriː/','There are three apples.','beginner','numbers','อังกฤษ'),(14,2,'Four','สี่','/fɔːr/','I see four birds.','beginner','numbers','อังกฤษ'),(15,2,'Five','ห้า','/faɪv/','Give me five minutes.','beginner','numbers','อังกฤษ'),(16,2,'Ten','สิบ','/tɛn/','I am ten years old.','beginner','numbers','อังกฤษ'),(17,2,'Twenty','ยี่สิบ','/ˈtwɛnti/','There are twenty students.','beginner','numbers','อังกฤษ'),(18,2,'Fifty','ห้าสิบ','/ˈfɪfti/','The book costs fifty baht.','beginner','numbers','อังกฤษ'),(19,2,'Hundred','ร้อย','/ˈhʌndrɪd/','I scored one hundred!','beginner','numbers','อังกฤษ'),(20,2,'Thousand','พัน','/ˈθaʊzənd/','A thousand stars in the sky.','beginner','numbers','อังกฤษ'),(21,3,'Monday','วันจันทร์','/ˈmʌndeɪ/','I go to school on Monday.','beginner','time','อังกฤษ'),(22,3,'Tuesday','วันอังคาร','/ˈtjuːzdeɪ/','We have English on Tuesday.','beginner','time','อังกฤษ'),(23,3,'Wednesday','วันพุธ','/ˈwɛnzdeɪ/','Wednesday is the middle of the week.','beginner','time','อังกฤษ'),(24,3,'Thursday','วันพฤหัสบดี','/ˈθɜːrzdeɪ/','Thursday comes after Wednesday.','beginner','time','อังกฤษ'),(25,3,'Friday','วันศุกร์','/ˈfraɪdeɪ/','I love Fridays!','beginner','time','อังกฤษ'),(26,3,'Saturday','วันเสาร์','/ˈsætərdeɪ/','We play football on Saturday.','beginner','time','อังกฤษ'),(27,3,'Sunday','วันอาทิตย์','/ˈsʌndeɪ/','Sunday is a day of rest.','beginner','time','อังกฤษ'),(28,3,'Today','วันนี้','/təˈdeɪ/','Today is a beautiful day.','beginner','time','อังกฤษ'),(29,3,'Tomorrow','พรุ่งนี้','/təˈmɒroʊ/','See you tomorrow!','beginner','time','อังกฤษ'),(30,3,'Yesterday','เมื่อวาน','/ˈjɛstərdeɪ/','I went to the park yesterday.','beginner','time','อังกฤษ'),(31,4,'Father','พ่อ','/ˈfɑːðər/','My father is a teacher.','beginner','family','อังกฤษ'),(32,4,'Mother','แม่','/ˈmʌðər/','My mother cooks delicious food.','beginner','family','อังกฤษ'),(33,4,'Brother','พี่/น้องชาย','/ˈbrʌðər/','My brother is older than me.','beginner','family','อังกฤษ'),(34,4,'Sister','พี่/น้องสาว','/ˈsɪstər/','My sister likes reading.','beginner','family','อังกฤษ'),(35,4,'Grandfather','ปู่/ตา','/ˈɡrændfɑːðər/','My grandfather tells great stories.','beginner','family','อังกฤษ'),(36,4,'Grandmother','ย่า/ยาย','/ˈɡrændmʌðər/','My grandmother makes cookies.','beginner','family','อังกฤษ'),(37,4,'Uncle','ลุง/อา','/ˈʌŋkl/','My uncle lives in Bangkok.','beginner','family','อังกฤษ'),(38,4,'Aunt','ป้า/น้า','/ænt/','My aunt is a doctor.','beginner','family','อังกฤษ'),(39,4,'Cousin','ลูกพี่ลูกน้อง','/ˈkʌzn/','I play with my cousin.','beginner','family','อังกฤษ'),(40,4,'Baby','ทารก','/ˈbeɪbi/','The baby is sleeping.','beginner','family','อังกฤษ'),(41,5,'Red','แดง','/rɛd/','The apple is red.','beginner','colors','อังกฤษ'),(42,5,'Blue','น้ำเงิน','/bluː/','The sky is blue.','beginner','colors','อังกฤษ'),(43,5,'Green','เขียว','/ɡriːn/','The grass is green.','beginner','colors','อังกฤษ'),(44,5,'Yellow','เหลือง','/ˈjɛloʊ/','The sun is yellow.','beginner','colors','อังกฤษ'),(45,5,'Orange','ส้ม','/ˈɒrɪndʒ/','I like orange juice.','beginner','colors','อังกฤษ'),(46,5,'Purple','ม่วง','/ˈpɜːrpl/','She wears a purple dress.','beginner','colors','อังกฤษ'),(47,5,'Pink','ชมพู','/pɪŋk/','The flower is pink.','beginner','colors','อังกฤษ'),(48,5,'Circle','วงกลม','/ˈsɜːrkl/','Draw a circle on the paper.','beginner','shapes','อังกฤษ'),(49,5,'Square','สี่เหลี่ยมจัตุรัส','/skwɛr/','A square has four equal sides.','beginner','shapes','อังกฤษ'),(50,5,'Triangle','สามเหลี่ยม','/ˈtraɪæŋɡl/','A triangle has three sides.','beginner','shapes','อังกฤษ'),(51,6,'Always','เสมอ','/ˈɔːlweɪz/','I always brush my teeth.','intermediate','adverbs','อังกฤษ'),(52,6,'Usually','ปกติ','/ˈjuːʒuəli/','She usually wakes up at 7.','intermediate','adverbs','อังกฤษ'),(53,6,'Sometimes','บางครั้ง','/ˈsʌmtaɪmz/','I sometimes eat pizza.','intermediate','adverbs','อังกฤษ'),(54,6,'Never','ไม่เคย','/ˈnɛvər/','He never lies.','intermediate','adverbs','อังกฤษ'),(55,6,'Often','บ่อย','/ˈɒfn/','We often go to the park.','intermediate','adverbs','อังกฤษ'),(56,7,'Visited','เยี่ยม (อดีต)','/ˈvɪzɪtɪd/','I visited my grandma last week.','intermediate','verbs','อังกฤษ'),(57,7,'Bought','ซื้อ (อดีต)','/bɔːt/','She bought a new bag.','intermediate','verbs','อังกฤษ'),(58,7,'Ate','กิน (อดีต)','/eɪt/','We ate sushi for dinner.','intermediate','verbs','อังกฤษ'),(59,7,'Went','ไป (อดีต)','/wɛnt/','They went to the beach.','intermediate','verbs','อังกฤษ'),(60,7,'Saw','เห็น (อดีต)','/sɔː/','I saw a rainbow yesterday.','intermediate','verbs','อังกฤษ'),(61,8,'Will','จะ','/wɪl/','I will help you.','intermediate','grammar','อังกฤษ'),(62,8,'Going to','กำลังจะ','/ˈɡoʊɪŋ tuː/','She is going to study tonight.','intermediate','grammar','อังกฤษ'),(63,8,'Shall','จะ (เสนอ)','/ʃæl/','Shall we go?','intermediate','grammar','อังกฤษ'),(64,8,'Probably','อาจจะ','/ˈprɒbəbli/','It will probably rain.','intermediate','grammar','อังกฤษ'),(65,8,'Definitely','แน่นอน','/ˈdɛfɪnɪtli/','I will definitely come.','intermediate','grammar','อังกฤษ'),(66,9,'Bigger','ใหญ่กว่า','/ˈbɪɡər/','An elephant is bigger than a cat.','intermediate','comparison','อังกฤษ'),(67,9,'Smaller','เล็กกว่า','/ˈsmɔːlər/','An ant is smaller than a dog.','intermediate','comparison','อังกฤษ'),(68,9,'Faster','เร็วกว่า','/ˈfæstər/','A car is faster than a bicycle.','intermediate','comparison','อังกฤษ'),(69,9,'Better','ดีกว่า','/ˈbɛtər/','This book is better than that one.','intermediate','comparison','อังกฤษ'),(70,9,'The best','ดีที่สุด','/ðə bɛst/','She is the best student.','intermediate','comparison','อังกฤษ'),(71,10,'Although','ถึงแม้ว่า','/ɔːlˈðoʊ/','Although it rained, we went out.','advanced','connectors','อังกฤษ'),(72,10,'However','อย่างไรก็ตาม','/haʊˈɛvər/','It was cold. However, we played outside.','advanced','connectors','อังกฤษ'),(73,10,'Therefore','ดังนั้น','/ˈðɛrfɔːr/','I studied hard; therefore, I passed.','advanced','connectors','อังกฤษ'),(74,10,'Meanwhile','ในขณะเดียวกัน','/ˈmiːnwaɪl/','She cooked. Meanwhile, I cleaned.','advanced','connectors','อังกฤษ'),(75,10,'Furthermore','นอกจากนี้','/ˈfɜːrðərmɔːr/','The food was good. Furthermore, it was cheap.','advanced','connectors','อังกฤษ'),(76,11,'Built','สร้าง (V3)','/bɪlt/','The house was built in 2020.','advanced','verbs','อังกฤษ'),(77,11,'Written','เขียน (V3)','/ˈrɪtn/','The book was written by her.','advanced','verbs','อังกฤษ'),(78,11,'Spoken','พูด (V3)','/ˈspoʊkən/','English is spoken worldwide.','advanced','verbs','อังกฤษ'),(79,11,'Taken','เอา (V3)','/ˈteɪkən/','The photo was taken yesterday.','advanced','verbs','อังกฤษ'),(80,11,'Chosen','เลือก (V3)','/ˈtʃoʊzn/','She was chosen as the leader.','advanced','verbs','อังกฤษ'),(81,12,'Claimed','อ้าง','/kleɪmd/','He claimed he was innocent.','advanced','verbs','อังกฤษ'),(82,12,'Mentioned','กล่าวถึง','/ˈmɛnʃənd/','She mentioned the meeting.','advanced','verbs','อังกฤษ'),(83,12,'Admitted','ยอมรับ','/ədˈmɪtɪd/','He admitted his mistake.','advanced','verbs','อังกฤษ'),(84,12,'Denied','ปฏิเสธ','/dɪˈnaɪd/','She denied the rumor.','advanced','verbs','อังกฤษ'),(85,12,'Suggested','แนะนำ','/səˈdʒɛstɪd/','He suggested going to the park.','advanced','verbs','อังกฤษ'),(86,NULL,'Dog','สุนัข','/dɒɡ/','The dog is running.','beginner','animals','อังกฤษ'),(87,NULL,'Cat','แมว','/kæt/','The cat is sleeping.','beginner','animals','อังกฤษ'),(88,NULL,'Bird','นก','/bɜːrd/','A bird is singing.','beginner','animals','อังกฤษ'),(89,NULL,'Fish','ปลา','/fɪʃ/','The fish is in the water.','beginner','animals','อังกฤษ'),(90,NULL,'Elephant','ช้าง','/ˈɛlɪfənt/','The elephant is big.','beginner','animals','อังกฤษ'),(91,NULL,'Apple','แอปเปิ้ล','/ˈæpl/','I eat an apple every day.','beginner','food','อังกฤษ'),(92,NULL,'Banana','กล้วย','/bəˈnænə/','Monkeys love bananas.','beginner','food','อังกฤษ'),(93,NULL,'Rice','ข้าว','/raɪs/','Thai people eat rice.','beginner','food','อังกฤษ'),(94,NULL,'Water','น้ำ','/ˈwɔːtər/','I drink water every day.','beginner','food','อังกฤษ'),(95,NULL,'Milk','นม','/mɪlk/','I drink milk in the morning.','beginner','food','อังกฤษ'),(96,NULL,'School','โรงเรียน','/skuːl/','I go to school at 8 AM.','beginner','places','อังกฤษ'),(97,NULL,'House','บ้าน','/haʊs/','My house is near the park.','beginner','places','อังกฤษ'),(98,NULL,'Park','สวนสาธารณะ','/pɑːrk/','We play in the park.','beginner','places','อังกฤษ'),(99,NULL,'Hospital','โรงพยาบาล','/ˈhɒspɪtl/','The doctor works at the hospital.','beginner','places','อังกฤษ'),(100,NULL,'Library','ห้องสมุด','/ˈlaɪbrəri/','I read books in the library.','beginner','places','อังกฤษ'),(101,NULL,'Happy','มีความสุข','/ˈhæpi/','I am happy today!','beginner','feelings','อังกฤษ'),(102,NULL,'Sad','เศร้า','/sæd/','She is sad because it rained.','beginner','feelings','อังกฤษ'),(103,NULL,'Angry','โกรธ','/ˈæŋɡri/','Don\'t be angry!','beginner','feelings','อังกฤษ'),(104,NULL,'Tired','เหนื่อย','/taɪərd/','I am tired after running.','beginner','feelings','อังกฤษ'),(105,NULL,'Excited','ตื่นเต้น','/ɪkˈsaɪtɪd/','I am excited about the trip!','beginner','feelings','อังกฤษ');
/*!40000 ALTER TABLE `vocabulary` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-23 19:47:46
