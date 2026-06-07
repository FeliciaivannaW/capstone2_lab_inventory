-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: localhost    Database: lab_inventory_db
-- ------------------------------------------------------
-- Server version	8.0.30

CREATE DATABASE IF NOT EXISTS `lab_inventory_db`;
USE `lab_inventory_db`;
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `asset_condition_logs`
--

DROP TABLE IF EXISTS `asset_condition_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asset_condition_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `inventory_asset_id` int NOT NULL,
  `updated_by` int NOT NULL,
  `old_condition` enum('baik','rusak_ringan','rusak_berat','maintenance','dihapus','diganti') DEFAULT NULL,
  `new_condition` enum('baik','rusak_ringan','rusak_berat','maintenance','dihapus','diganti') NOT NULL,
  `note` text,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_asset_condition_logs_asset` (`inventory_asset_id`),
  KEY `fk_asset_condition_logs_updated_by` (`updated_by`),
  CONSTRAINT `fk_asset_condition_logs_asset` FOREIGN KEY (`inventory_asset_id`) REFERENCES `inventory_assets` (`id`),
  CONSTRAINT `fk_asset_condition_logs_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `asset_disposals`
--

DROP TABLE IF EXISTS `asset_disposals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asset_disposals` (
  `id` int NOT NULL AUTO_INCREMENT,
  `inventory_asset_id` int NOT NULL,
  `disposed_by` int NOT NULL,
  `disposal_date` date NOT NULL,
  `reason` text NOT NULL,
  `disposal_note` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_asset_disposals_asset` (`inventory_asset_id`),
  KEY `fk_asset_disposals_disposed_by` (`disposed_by`),
  CONSTRAINT `fk_asset_disposals_asset` FOREIGN KEY (`inventory_asset_id`) REFERENCES `inventory_assets` (`id`),
  CONSTRAINT `fk_asset_disposals_disposed_by` FOREIGN KEY (`disposed_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bhp_stock_movements`
--

DROP TABLE IF EXISTS `bhp_stock_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bhp_stock_movements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `stock_id` int NOT NULL,
  `procurement_item_id` int DEFAULT NULL,
  `receipt_id` int DEFAULT NULL,
  `maintenance_id` int DEFAULT NULL,
  `performed_by` int NOT NULL,
  `movement_type` enum('in','out','adjustment','maintenance_usage') NOT NULL,
  `quantity` int NOT NULL,
  `movement_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `note` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_bhp_stock_movements_stock` (`stock_id`),
  KEY `fk_bhp_stock_movements_procurement_item` (`procurement_item_id`),
  KEY `fk_bhp_stock_movements_receipt` (`receipt_id`),
  KEY `fk_bhp_stock_movements_maintenance` (`maintenance_id`),
  KEY `fk_bhp_stock_movements_performed_by` (`performed_by`),
  CONSTRAINT `fk_bhp_stock_movements_maintenance` FOREIGN KEY (`maintenance_id`) REFERENCES `maintenance_logs` (`id`),
  CONSTRAINT `fk_bhp_stock_movements_performed_by` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_bhp_stock_movements_procurement_item` FOREIGN KEY (`procurement_item_id`) REFERENCES `procurement_items` (`id`),
  CONSTRAINT `fk_bhp_stock_movements_receipt` FOREIGN KEY (`receipt_id`) REFERENCES `goods_receipts` (`id`),
  CONSTRAINT `fk_bhp_stock_movements_stock` FOREIGN KEY (`stock_id`) REFERENCES `bhp_stocks` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bhp_stocks`
--

DROP TABLE IF EXISTS `bhp_stocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bhp_stocks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `lab_id` int NOT NULL,
  `item_catalog_id` int NOT NULL,
  `current_stock` int NOT NULL DEFAULT '0',
  `minimum_stock` int NOT NULL DEFAULT '0',
  `unit` varchar(50) NOT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_bhp_stock_per_lab_item` (`lab_id`,`item_catalog_id`),
  KEY `fk_bhp_stocks_catalog` (`item_catalog_id`),
  CONSTRAINT `fk_bhp_stocks_catalog` FOREIGN KEY (`item_catalog_id`) REFERENCES `item_catalogs` (`id`),
  CONSTRAINT `fk_bhp_stocks_lab` FOREIGN KEY (`lab_id`) REFERENCES `laboratories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `buildings`
--

DROP TABLE IF EXISTS `buildings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `buildings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `code` varchar(50) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `description` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `idx_cache_expiration` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `idx_cache_locks_expiration` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `floors`
--

DROP TABLE IF EXISTS `floors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `floors` (
  `id` int NOT NULL AUTO_INCREMENT,
  `building_id` int NOT NULL,
  `floor_number` int NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_floor_per_building` (`building_id`,`floor_number`),
  CONSTRAINT `fk_floors_building` FOREIGN KEY (`building_id`) REFERENCES `buildings` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `goods_receipts`
--

DROP TABLE IF EXISTS `goods_receipts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `goods_receipts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `procurement_item_id` int NOT NULL,
  `received_by` int NOT NULL,
  `received_date` date NOT NULL,
  `quantity_received` int NOT NULL,
  `note` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_goods_receipts_procurement_item` (`procurement_item_id`),
  KEY `fk_goods_receipts_received_by` (`received_by`),
  CONSTRAINT `fk_goods_receipts_procurement_item` FOREIGN KEY (`procurement_item_id`) REFERENCES `procurement_items` (`id`),
  CONSTRAINT `fk_goods_receipts_received_by` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inventory_assets`
--

DROP TABLE IF EXISTS `inventory_assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_assets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `item_catalog_id` int NOT NULL,
  `procurement_item_id` int DEFAULT NULL,
  `receipt_id` int DEFAULT NULL,
  `room_id` int DEFAULT NULL,
  `replaced_by_asset_id` int DEFAULT NULL,
  `asset_code` varchar(100) NOT NULL,
  `label_number` varchar(100) DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `barcode` varchar(255) DEFAULT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `purchase_price` decimal(12,2) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `received_date` date DEFAULT NULL,
  `asset_condition` enum('baik','rusak_ringan','rusak_berat','maintenance','dihapus','diganti') NOT NULL DEFAULT 'baik',
  `status` enum('received','labeled','available','in_use','maintenance','disposed','replaced') NOT NULL DEFAULT 'received',
  `photo_url` varchar(255) DEFAULT NULL,
  `notes` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `asset_code` (`asset_code`),
  KEY `fk_inventory_assets_catalog` (`item_catalog_id`),
  KEY `fk_inventory_assets_procurement_item` (`procurement_item_id`),
  KEY `fk_inventory_assets_receipt` (`receipt_id`),
  KEY `fk_inventory_assets_room` (`room_id`),
  KEY `fk_inventory_assets_replaced_by` (`replaced_by_asset_id`),
  CONSTRAINT `fk_inventory_assets_catalog` FOREIGN KEY (`item_catalog_id`) REFERENCES `item_catalogs` (`id`),
  CONSTRAINT `fk_inventory_assets_procurement_item` FOREIGN KEY (`procurement_item_id`) REFERENCES `procurement_items` (`id`),
  CONSTRAINT `fk_inventory_assets_receipt` FOREIGN KEY (`receipt_id`) REFERENCES `goods_receipts` (`id`),
  CONSTRAINT `fk_inventory_assets_replaced_by` FOREIGN KEY (`replaced_by_asset_id`) REFERENCES `inventory_assets` (`id`),
  CONSTRAINT `fk_inventory_assets_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `item_catalogs`
--

DROP TABLE IF EXISTS `item_catalogs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `item_catalogs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `type` enum('inventory','bhp') NOT NULL,
  `unit` varchar(50) NOT NULL,
  `description` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_item_catalogs_category` (`category_id`),
  CONSTRAINT `fk_item_catalogs_category` FOREIGN KEY (`category_id`) REFERENCES `item_categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `item_categories`
--

DROP TABLE IF EXISTS `item_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `item_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `description` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lab_group_rooms`
--

DROP TABLE IF EXISTS `lab_group_rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lab_group_rooms` (
  `id` int NOT NULL AUTO_INCREMENT,
  `group_id` int NOT NULL,
  `room_id` int NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_lab_group_room` (`group_id`,`room_id`),
  KEY `fk_lab_group_rooms_room` (`room_id`),
  CONSTRAINT `fk_lab_group_rooms_group` FOREIGN KEY (`group_id`) REFERENCES `lab_groups` (`id`),
  CONSTRAINT `fk_lab_group_rooms_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lab_group_users`
--

DROP TABLE IF EXISTS `lab_group_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lab_group_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `group_id` int NOT NULL,
  `user_id` int NOT NULL,
  `role_in_group` enum('kepala_lab','staf_lab') NOT NULL DEFAULT 'staf_lab',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_lab_group_user` (`group_id`,`user_id`),
  KEY `fk_lab_group_users_user` (`user_id`),
  CONSTRAINT `fk_lab_group_users_group` FOREIGN KEY (`group_id`) REFERENCES `lab_groups` (`id`),
  CONSTRAINT `fk_lab_group_users_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lab_groups`
--

DROP TABLE IF EXISTS `lab_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lab_groups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `laboratory_id` int NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_lab_group_name_per_lab` (`laboratory_id`,`name`),
  CONSTRAINT `fk_lab_groups_laboratory` FOREIGN KEY (`laboratory_id`) REFERENCES `laboratories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

-- Table structure for table `lab_group_laboratories`

DROP TABLE IF EXISTS `lab_group_laboratories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lab_group_laboratories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `group_id` int NOT NULL,
  `laboratory_id` int NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_lab_group_laboratory` (`group_id`,`laboratory_id`),
  KEY `fk_lab_group_laboratories_laboratory` (`laboratory_id`),
  CONSTRAINT `fk_lab_group_laboratories_group` FOREIGN KEY (`group_id`) REFERENCES `lab_groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_lab_group_laboratories_laboratory` FOREIGN KEY (`laboratory_id`) REFERENCES `laboratories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `laboratories`
--

DROP TABLE IF EXISTS `laboratories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `laboratories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `room_id` int NOT NULL,
  `head_user_id` int DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `code` varchar(50) NOT NULL,
  `description` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `room_id` (`room_id`),
  UNIQUE KEY `code` (`code`),
  KEY `fk_laboratories_head_user` (`head_user_id`),
  CONSTRAINT `fk_laboratories_head_user` FOREIGN KEY (`head_user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_laboratories_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `maintenance_logs`
--

DROP TABLE IF EXISTS `maintenance_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `maintenance_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `inventory_asset_id` int NOT NULL,
  `performed_by` int NOT NULL,
  `maintenance_date` date NOT NULL,
  `issue_description` text,
  `action_taken` text,
  `condition_before` enum('baik','rusak_ringan','rusak_berat','maintenance','dihapus','diganti') DEFAULT NULL,
  `condition_after` enum('baik','rusak_ringan','rusak_berat','maintenance','dihapus','diganti') DEFAULT NULL,
  `status` enum('planned','in_progress','done','cancelled') NOT NULL DEFAULT 'planned',
  `cost` decimal(12,2) DEFAULT NULL,
  `notes` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_maintenance_logs_asset` (`inventory_asset_id`),
  KEY `fk_maintenance_logs_performed_by` (`performed_by`),
  CONSTRAINT `fk_maintenance_logs_asset` FOREIGN KEY (`inventory_asset_id`) REFERENCES `inventory_assets` (`id`),
  CONSTRAINT `fk_maintenance_logs_performed_by` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `procurement_drafts`
--

DROP TABLE IF EXISTS `procurement_drafts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `procurement_drafts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `lab_id` int NOT NULL,
  `created_by` int NOT NULL,
  `finalized_by` int DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `budget_year` year NOT NULL,
  `status` enum('draft','submitted','finalized','rejected') NOT NULL DEFAULT 'draft',
  `is_locked` tinyint(1) NOT NULL DEFAULT '0',
  `notes` text,
  `submitted_at` datetime DEFAULT NULL,
  `finalized_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_procurement_drafts_lab` (`lab_id`),
  KEY `fk_procurement_drafts_created_by` (`created_by`),
  KEY `fk_procurement_drafts_finalized_by` (`finalized_by`),
  CONSTRAINT `fk_procurement_drafts_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_procurement_drafts_finalized_by` FOREIGN KEY (`finalized_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_procurement_drafts_lab` FOREIGN KEY (`lab_id`) REFERENCES `laboratories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `procurement_items`
--

DROP TABLE IF EXISTS `procurement_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `procurement_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `draft_id` int NOT NULL,
  `item_catalog_id` int DEFAULT NULL,
  `replacement_asset_id` int DEFAULT NULL,
  `reviewed_by` int DEFAULT NULL,
  `item_name` varchar(150) NOT NULL,
  `item_description` text,
  `item_type` enum('inventory','bhp') NOT NULL,
  `quantity` int NOT NULL,
  `estimated_price` decimal(12,2) NOT NULL,
  `purchase_link` varchar(255) DEFAULT NULL,
  `review_status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `review_note` text,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_procurement_items_draft` (`draft_id`),
  KEY `fk_procurement_items_catalog` (`item_catalog_id`),
  KEY `fk_procurement_items_reviewed_by` (`reviewed_by`),
  KEY `fk_procurement_items_replacement_asset` (`replacement_asset_id`),
  CONSTRAINT `fk_procurement_items_catalog` FOREIGN KEY (`item_catalog_id`) REFERENCES `item_catalogs` (`id`),
  CONSTRAINT `fk_procurement_items_draft` FOREIGN KEY (`draft_id`) REFERENCES `procurement_drafts` (`id`),
  CONSTRAINT `fk_procurement_items_replacement_asset` FOREIGN KEY (`replacement_asset_id`) REFERENCES `inventory_assets` (`id`),
  CONSTRAINT `fk_procurement_items_reviewed_by` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `room_types`
--

DROP TABLE IF EXISTS `room_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `room_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rooms`
--

DROP TABLE IF EXISTS `rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rooms` (
  `id` int NOT NULL AUTO_INCREMENT,
  `floor_id` int NOT NULL,
  `room_type_id` int NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(150) NOT NULL,
  `capacity` int DEFAULT NULL,
  `description` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `fk_rooms_floor` (`floor_id`),
  KEY `fk_rooms_room_type` (`room_type_id`),
  CONSTRAINT `fk_rooms_floor` FOREIGN KEY (`floor_id`) REFERENCES `floors` (`id`),
  CONSTRAINT `fk_rooms_room_type` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `payload` longtext NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sessions_user_id` (`user_id`),
  KEY `idx_sessions_last_activity` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `role_id` int NOT NULL,
  `lab_id` int DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `nrp_nip` varchar(50) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `fk_users_role` (`role_id`),
  KEY `fk_users_lab` (`lab_id`),
  CONSTRAINT `fk_users_lab` FOREIGN KEY (`lab_id`) REFERENCES `laboratories` (`id`),
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-02 10:04:46
