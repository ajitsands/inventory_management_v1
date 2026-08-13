-- Inventory Management System Production Database Setup Script
-- Generated: 2026-08-13 21:43:52
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `vendors`;
DROP TABLE IF EXISTS `vendor_quotations`;
DROP TABLE IF EXISTS `vendor_quotation_items`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `system_settings`;
DROP TABLE IF EXISTS `system_sequences`;
DROP TABLE IF EXISTS `system_audit_trail`;
DROP TABLE IF EXISTS `stock_transfers`;
DROP TABLE IF EXISTS `stock_transfer_items`;
DROP TABLE IF EXISTS `stock_returns`;
DROP TABLE IF EXISTS `stock_return_wallets`;
DROP TABLE IF EXISTS `stock_return_rejections`;
DROP TABLE IF EXISTS `stock_return_items`;
DROP TABLE IF EXISTS `stock_movements_ledger`;
DROP TABLE IF EXISTS `sales_invoices`;
DROP TABLE IF EXISTS `sales_invoice_items`;
DROP TABLE IF EXISTS `purchase_invoices`;
DROP TABLE IF EXISTS `purchase_invoice_items`;
DROP TABLE IF EXISTS `locations`;
DROP TABLE IF EXISTS `location_batch_stock`;
DROP TABLE IF EXISTS `items`;
DROP TABLE IF EXISTS `item_batches`;
DROP TABLE IF EXISTS `invoice_payment_records`;
DROP TABLE IF EXISTS `doctors`;
DROP TABLE IF EXISTS `damaged_stock`;
DROP TABLE IF EXISTS `customers`;
DROP TABLE IF EXISTS `credit_notes`;
DROP TABLE IF EXISTS `credit_note_items`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `branch_payments`;

SET FOREIGN_KEY_CHECKS = 1;

-- Table structure for table `branch_payments`
CREATE TABLE `branch_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transfer_id` int(11) NOT NULL,
  `amount` decimal(15,3) NOT NULL DEFAULT '0.000',
  `payment_method` enum('CASH','BANK_TRANSFER','CHEQUE','CREDIT_CARD') NOT NULL DEFAULT 'CASH',
  `reference_no` varchar(100) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `cheque_date` date DEFAULT NULL,
  `notes` text,
  `received_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `transfer_id` (`transfer_id`),
  CONSTRAINT `branch_payments_ibfk_1` FOREIGN KEY (`transfer_id`) REFERENCES `stock_transfers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Table structure for table `categories`
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(50) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Seed data for table `categories`
INSERT INTO `categories` (`id`, `name`, `code`, `description`) VALUES ('1', 'Pharmaceuticals & Medicines', 'CAT-MED', 'Prescription drugs, antibiotics, pain relievers, and syrups');
INSERT INTO `categories` (`id`, `name`, `code`, `description`) VALUES ('2', 'Medical Consumables', 'CAT-CON', 'Syringes, bandages, gloves, surgical drapes, and IV sets');
INSERT INTO `categories` (`id`, `name`, `code`, `description`) VALUES ('3', 'Diagnostic Equipment', 'CAT-DIAG', 'Blood glucose monitors, test strips, thermometers, and BP cuffs');

-- Table structure for table `credit_note_items`
CREATE TABLE `credit_note_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `credit_note_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `batch_id` int(11) NOT NULL,
  `batch_code` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_rate` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `total_amount` decimal(15,4) NOT NULL DEFAULT '0.0000',
  PRIMARY KEY (`id`),
  KEY `credit_note_id` (`credit_note_id`),
  KEY `item_id` (`item_id`),
  KEY `batch_id` (`batch_id`),
  CONSTRAINT `credit_note_items_ibfk_1` FOREIGN KEY (`credit_note_id`) REFERENCES `credit_notes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `credit_note_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `credit_note_items_ibfk_3` FOREIGN KEY (`batch_id`) REFERENCES `item_batches` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Table structure for table `credit_notes`
CREATE TABLE `credit_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `credit_note_no` varchar(50) NOT NULL,
  `return_id` int(11) NOT NULL,
  `branch_location_id` int(11) NOT NULL,
  `original_transfer_no` varchar(50) DEFAULT NULL,
  `total_amount` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `reason` text,
  `created_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `credit_note_no` (`credit_note_no`),
  KEY `return_id` (`return_id`),
  KEY `branch_location_id` (`branch_location_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `credit_notes_ibfk_1` FOREIGN KEY (`return_id`) REFERENCES `stock_returns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `credit_notes_ibfk_2` FOREIGN KEY (`branch_location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `credit_notes_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Table structure for table `customers`
CREATE TABLE `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `code` varchar(50) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `address` text,
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Seed data for table `customers`
INSERT INTO `customers` (`id`, `name`, `code`, `phone`, `email`, `address`, `status`, `created_at`) VALUES ('1', 'Walk-in General Customer', 'CUST-001', '+1 555-0101', 'walkin@patient.org', 'OPD Clinic Desk', 'ACTIVE', '2026-08-12 00:33:34');
INSERT INTO `customers` (`id`, `name`, `code`, `phone`, `email`, `address`, `status`, `created_at`) VALUES ('2', 'John Doe (Patient #102)', 'CUST-002', '+1 555-0102', 'john.doe@email.com', '12 Maple Street', 'ACTIVE', '2026-08-12 00:33:34');
INSERT INTO `customers` (`id`, `name`, `code`, `phone`, `email`, `address`, `status`, `created_at`) VALUES ('3', 'Jane Smith (Patient #103)', 'CUST-003', '+1 555-0103', 'jane.smith@email.com', '88 Oak Ridge Way', 'ACTIVE', '2026-08-12 00:33:34');
INSERT INTO `customers` (`id`, `name`, `code`, `phone`, `email`, `address`, `status`, `created_at`) VALUES ('4', 'St. Jude Health Insurance Client', 'CUST-004', '+1 555-0104', 'billing@stjude-health.org', 'Corporate HQ Suite 400', 'ACTIVE', '2026-08-12 00:33:34');

-- Table structure for table `damaged_stock`
CREATE TABLE `damaged_stock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `return_id` int(11) NOT NULL,
  `return_item_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `batch_id` int(11) NOT NULL,
  `batch_code` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `reason` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `return_id` (`return_id`),
  KEY `location_id` (`location_id`),
  KEY `item_id` (`item_id`),
  KEY `batch_id` (`batch_id`),
  CONSTRAINT `damaged_stock_ibfk_1` FOREIGN KEY (`return_id`) REFERENCES `stock_returns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `damaged_stock_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `damaged_stock_ibfk_3` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `damaged_stock_ibfk_4` FOREIGN KEY (`batch_id`) REFERENCES `item_batches` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Table structure for table `doctors`
CREATE TABLE `doctors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `doctor_code` varchar(50) NOT NULL,
  `name` varchar(150) NOT NULL,
  `speciality` varchar(100) DEFAULT 'General Physician',
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `location_id` int(11) NOT NULL,
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `doctor_code` (`doctor_code`),
  KEY `location_id` (`location_id`),
  CONSTRAINT `doctors_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Seed data for table `doctors`
INSERT INTO `doctors` (`id`, `doctor_code`, `name`, `speciality`, `phone`, `email`, `location_id`, `status`, `created_at`) VALUES ('1', 'DOC-001', 'Dr. Alexander Smith', 'General Physician / OPD', '+973 1700-1111', 'dr.smith@organization.org', '4', 'ACTIVE', '2026-08-12 02:50:44');
INSERT INTO `doctors` (`id`, `doctor_code`, `name`, `speciality`, `phone`, `email`, `location_id`, `status`, `created_at`) VALUES ('2', 'DOC-002', 'Dr. Sarah Johnson', 'Pediatric Specialist', '+973 1700-2222', 'dr.sarah@organization.org', '4', 'ACTIVE', '2026-08-12 02:50:44');
INSERT INTO `doctors` (`id`, `doctor_code`, `name`, `speciality`, `phone`, `email`, `location_id`, `status`, `created_at`) VALUES ('3', 'DOC-003', 'Dr. Ahmed Al-Mansoori', 'Consultant Cardiologist', '+973 1700-3333', 'dr.ahmed@organization.org', '5', 'ACTIVE', '2026-08-12 02:50:44');

-- Table structure for table `invoice_payment_records`
CREATE TABLE `invoice_payment_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transfer_id` int(11) NOT NULL,
  `invoice_no` varchar(80) NOT NULL,
  `amount_paid` decimal(15,3) NOT NULL DEFAULT '0.000',
  `payment_method` enum('CASH','BANK_TRANSFER','CHEQUE') NOT NULL DEFAULT 'CASH',
  `bank_name` varchar(150) DEFAULT NULL,
  `bank_reference` varchar(100) DEFAULT NULL,
  `cheque_no` varchar(80) DEFAULT NULL,
  `cheque_date` date DEFAULT NULL,
  `remarks` text,
  `created_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `idx_pay_transfer` (`transfer_id`),
  KEY `idx_pay_invoice` (`invoice_no`),
  CONSTRAINT `invoice_payment_records_ibfk_1` FOREIGN KEY (`transfer_id`) REFERENCES `stock_transfers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoice_payment_records_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Table structure for table `item_batches`
CREATE TABLE `item_batches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `batch_code` varchar(80) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `purchase_price` decimal(15,3) NOT NULL DEFAULT '0.000',
  `selling_price` decimal(15,3) NOT NULL DEFAULT '0.000',
  `mrp` decimal(15,3) NOT NULL DEFAULT '0.000',
  `manufacture_date` date DEFAULT NULL,
  `expiry_date` date NOT NULL,
  `purchase_date` date NOT NULL,
  `initial_qty` int(11) NOT NULL DEFAULT '0',
  `current_qty` int(11) NOT NULL DEFAULT '0',
  `status` enum('ACTIVE','EXPIRED','DEPLETED') DEFAULT 'ACTIVE',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  KEY `vendor_id` (`vendor_id`),
  KEY `idx_batch_code` (`batch_code`),
  KEY `idx_expiry_date` (`expiry_date`),
  CONSTRAINT `item_batches_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  CONSTRAINT `item_batches_ibfk_2` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Table structure for table `items`
CREATE TABLE `items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_code` varchar(50) NOT NULL,
  `name` varchar(200) NOT NULL,
  `category_id` int(11) NOT NULL,
  `unit_of_measure` varchar(30) DEFAULT 'Unit',
  `min_reorder_level` int(11) DEFAULT '10',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `item_code` (`item_code`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Seed data for table `items`
INSERT INTO `items` (`id`, `item_code`, `name`, `category_id`, `unit_of_measure`, `min_reorder_level`, `created_at`) VALUES ('1', 'MED-PAR-500', 'Paracetamol 500mg Tablets (Box of 100)', '1', 'Box', '20', '2026-08-11 21:04:48');
INSERT INTO `items` (`id`, `item_code`, `name`, `category_id`, `unit_of_measure`, `min_reorder_level`, `created_at`) VALUES ('2', 'MED-AMO-500', 'Amoxicillin 500mg Capsules (Box of 50)', '1', 'Box', '15', '2026-08-11 21:04:48');
INSERT INTO `items` (`id`, `item_code`, `name`, `category_id`, `unit_of_measure`, `min_reorder_level`, `created_at`) VALUES ('3', 'MED-AZI-250', 'Azithromycin 250mg Tablets (Box of 30)', '1', 'Box', '10', '2026-08-11 21:04:48');
INSERT INTO `items` (`id`, `item_code`, `name`, `category_id`, `unit_of_measure`, `min_reorder_level`, `created_at`) VALUES ('4', 'CON-GLV-LAT', 'Latex Examination Gloves (Box of 100)', '2', 'Box', '50', '2026-08-11 21:04:48');
INSERT INTO `items` (`id`, `item_code`, `name`, `category_id`, `unit_of_measure`, `min_reorder_level`, `created_at`) VALUES ('5', 'CON-SYR-05M', 'Sterile Syringe 5ml (Pack of 50)', '2', 'Pack', '30', '2026-08-11 21:04:48');
INSERT INTO `items` (`id`, `item_code`, `name`, `category_id`, `unit_of_measure`, `min_reorder_level`, `created_at`) VALUES ('6', 'DIA-GLU-STR', 'Blood Glucose Test Strips (Pack of 50)', '3', 'Pack', '15', '2026-08-11 21:04:48');

-- Table structure for table `location_batch_stock`
CREATE TABLE `location_batch_stock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `location_id` int(11) NOT NULL,
  `batch_id` int(11) NOT NULL,
  `quantity_available` int(11) NOT NULL DEFAULT '0',
  `last_updated` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_location_batch` (`location_id`,`batch_id`),
  KEY `batch_id` (`batch_id`),
  CONSTRAINT `location_batch_stock_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`),
  CONSTRAINT `location_batch_stock_ibfk_2` FOREIGN KEY (`batch_id`) REFERENCES `item_batches` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Table structure for table `locations`
CREATE TABLE `locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `code` varchar(50) NOT NULL,
  `type` enum('MAIN_BRANCH','SUB_BRANCH','CLINIC') NOT NULL,
  `address` text,
  `phone` varchar(30) DEFAULT NULL,
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Seed data for table `locations`
INSERT INTO `locations` (`id`, `name`, `code`, `type`, `address`, `phone`, `status`, `created_at`) VALUES ('1', 'Central Main Warehouse & Branch', 'LOC-MAIN-01', 'MAIN_BRANCH', '100 Central Avenue, Tech City', '+1 800-555-0100', 'ACTIVE', '2026-08-11 21:04:48');
INSERT INTO `locations` (`id`, `name`, `code`, `type`, `address`, `phone`, `status`, `created_at`) VALUES ('2', 'North Regional Sub-Branch', 'LOC-SUB-01', 'SUB_BRANCH', '45 North Hub, Metro Region', '+1 800-555-0200', 'ACTIVE', '2026-08-11 21:04:48');
INSERT INTO `locations` (`id`, `name`, `code`, `type`, `address`, `phone`, `status`, `created_at`) VALUES ('3', 'South Regional Sub-Branch', 'LOC-SUB-02', 'SUB_BRANCH', '88 South Depot, Commercial Zone', '+1 800-555-0300', 'ACTIVE', '2026-08-11 21:04:48');
INSERT INTO `locations` (`id`, `name`, `code`, `type`, `address`, `phone`, `status`, `created_at`) VALUES ('4', 'City Wellness Clinic Outlet #1', 'LOC-CLN-01', 'CLINIC', '12 Downtown Medical Complex', '+1 800-555-0401', 'ACTIVE', '2026-08-11 21:04:48');
INSERT INTO `locations` (`id`, `name`, `code`, `type`, `address`, `phone`, `status`, `created_at`) VALUES ('5', 'Metro Care Clinic Outlet #2', 'LOC-CLN-02', 'CLINIC', '99 Metro Care Center, East Wing', '+1 800-555-0402', 'ACTIVE', '2026-08-11 21:04:48');

-- Table structure for table `purchase_invoice_items`
CREATE TABLE `purchase_invoice_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `purchase_invoice_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `batch_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `purchase_price` decimal(15,3) NOT NULL DEFAULT '0.000',
  `selling_price` decimal(15,3) NOT NULL DEFAULT '0.000',
  `mrp` decimal(15,3) NOT NULL DEFAULT '0.000',
  `expiry_date` date NOT NULL,
  `subtotal` decimal(15,3) NOT NULL DEFAULT '0.000',
  PRIMARY KEY (`id`),
  KEY `purchase_invoice_id` (`purchase_invoice_id`),
  KEY `item_id` (`item_id`),
  KEY `batch_id` (`batch_id`),
  CONSTRAINT `purchase_invoice_items_ibfk_1` FOREIGN KEY (`purchase_invoice_id`) REFERENCES `purchase_invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_invoice_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  CONSTRAINT `purchase_invoice_items_ibfk_3` FOREIGN KEY (`batch_id`) REFERENCES `item_batches` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Table structure for table `purchase_invoices`
CREATE TABLE `purchase_invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_no` varchar(80) NOT NULL,
  `po_no` varchar(80) NOT NULL,
  `po_date` date NOT NULL,
  `vendor_invoice_no` varchar(80) NOT NULL,
  `vendor_invoice_date` date NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `total_amount` decimal(15,3) NOT NULL DEFAULT '0.000',
  `remarks` text,
  `document_url` varchar(255) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_no` (`invoice_no`),
  KEY `vendor_id` (`vendor_id`),
  KEY `location_id` (`location_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `purchase_invoices_ibfk_1` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`),
  CONSTRAINT `purchase_invoices_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`),
  CONSTRAINT `purchase_invoices_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Table structure for table `sales_invoice_items`
CREATE TABLE `sales_invoice_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sales_invoice_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `batch_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `unit_price` decimal(15,3) NOT NULL DEFAULT '0.000',
  `subtotal` decimal(15,3) NOT NULL DEFAULT '0.000',
  PRIMARY KEY (`id`),
  KEY `sales_invoice_id` (`sales_invoice_id`),
  KEY `item_id` (`item_id`),
  KEY `batch_id` (`batch_id`),
  CONSTRAINT `sales_invoice_items_ibfk_1` FOREIGN KEY (`sales_invoice_id`) REFERENCES `sales_invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sales_invoice_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  CONSTRAINT `sales_invoice_items_ibfk_3` FOREIGN KEY (`batch_id`) REFERENCES `item_batches` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Table structure for table `sales_invoices`
CREATE TABLE `sales_invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sales_invoice_no` varchar(80) NOT NULL,
  `clinic_location_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_name` varchar(150) NOT NULL DEFAULT 'Walk-in Customer',
  `customer_phone` varchar(30) DEFAULT NULL,
  `doctor_name` varchar(150) DEFAULT 'OPD Doctor',
  `total_amount` decimal(15,3) NOT NULL DEFAULT '0.000',
  `discount` decimal(15,3) NOT NULL DEFAULT '0.000',
  `net_amount` decimal(15,3) NOT NULL DEFAULT '0.000',
  `payment_method` varchar(50) DEFAULT 'CASH',
  `created_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sales_invoice_no` (`sales_invoice_no`),
  KEY `clinic_location_id` (`clinic_location_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `sales_invoices_ibfk_1` FOREIGN KEY (`clinic_location_id`) REFERENCES `locations` (`id`),
  CONSTRAINT `sales_invoices_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Table structure for table `stock_movements_ledger`
CREATE TABLE `stock_movements_ledger` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_type` enum('PURCHASE','BRANCH_TRANSFER','CLINIC_TRANSFER','CUSTOMER_SALE','STOCK_RETURN','STOCK_RETURN_IN','STOCK_RETURN_OUT','STOCK_RETURN_VENDOR','STOCK_DAMAGED','STOCK_RESTORE_IN','ADJUSTMENT') DEFAULT NULL,
  `reference_no` varchar(80) NOT NULL,
  `item_id` int(11) NOT NULL,
  `batch_id` int(11) NOT NULL,
  `from_location_id` int(11) DEFAULT NULL,
  `to_location_id` int(11) DEFAULT NULL,
  `qty` int(11) NOT NULL,
  `unit_cost` decimal(15,3) NOT NULL DEFAULT '0.000',
  `unit_price` decimal(15,3) NOT NULL DEFAULT '0.000',
  `timestamp` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_by` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  KEY `created_by` (`created_by`),
  KEY `idx_mov_batch` (`batch_id`),
  KEY `idx_mov_ref` (`reference_no`),
  CONSTRAINT `stock_movements_ledger_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  CONSTRAINT `stock_movements_ledger_ibfk_2` FOREIGN KEY (`batch_id`) REFERENCES `item_batches` (`id`),
  CONSTRAINT `stock_movements_ledger_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Table structure for table `stock_return_items`
CREATE TABLE `stock_return_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `return_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `batch_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `unit_price` decimal(15,3) NOT NULL DEFAULT '0.000',
  `subtotal` decimal(15,3) NOT NULL DEFAULT '0.000',
  `batch_code` varchar(100) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT '0',
  `accepted_qty` int(11) DEFAULT '0',
  `rejected_qty` int(11) DEFAULT '0',
  `unit_rate` decimal(15,4) DEFAULT '0.0000',
  `total_amount` decimal(15,4) DEFAULT '0.0000',
  `status` enum('PENDING','ACCEPTED','REJECTED','RESTORED','RESTORED_TO_STOCK') NOT NULL DEFAULT 'PENDING',
  PRIMARY KEY (`id`),
  KEY `return_id` (`return_id`),
  KEY `item_id` (`item_id`),
  KEY `batch_id` (`batch_id`),
  CONSTRAINT `stock_return_items_ibfk_1` FOREIGN KEY (`return_id`) REFERENCES `stock_returns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stock_return_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  CONSTRAINT `stock_return_items_ibfk_3` FOREIGN KEY (`batch_id`) REFERENCES `item_batches` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Table structure for table `stock_return_rejections`
CREATE TABLE `stock_return_rejections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `return_id` int(11) NOT NULL,
  `return_item_id` int(11) NOT NULL,
  `clinic_location_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `batch_id` int(11) NOT NULL,
  `batch_code` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `rejection_reason` text,
  `status` enum('IN_REJECT_WALLET','RESTORED','RESTORED_TO_STOCK') NOT NULL DEFAULT 'IN_REJECT_WALLET',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `restored_at` datetime DEFAULT NULL,
  `restored_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `return_id` (`return_id`),
  KEY `clinic_location_id` (`clinic_location_id`),
  KEY `item_id` (`item_id`),
  KEY `batch_id` (`batch_id`),
  CONSTRAINT `stock_return_rejections_ibfk_1` FOREIGN KEY (`return_id`) REFERENCES `stock_returns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stock_return_rejections_ibfk_2` FOREIGN KEY (`clinic_location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stock_return_rejections_ibfk_3` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `stock_return_rejections_ibfk_4` FOREIGN KEY (`batch_id`) REFERENCES `item_batches` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Table structure for table `stock_return_wallets`
CREATE TABLE `stock_return_wallets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `return_id` int(11) NOT NULL,
  `return_item_id` int(11) NOT NULL,
  `target_location_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `batch_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `wallet_type` enum('PENDING_RETURN','CLINIC_REJECT','MAIN_STORE_DAMAGED') NOT NULL DEFAULT 'PENDING_RETURN',
  `status` enum('PENDING','ACCEPTED','REJECTED','RESTORED_TO_STOCK') NOT NULL DEFAULT 'PENDING',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `return_id` (`return_id`),
  KEY `return_item_id` (`return_item_id`),
  KEY `target_location_id` (`target_location_id`),
  KEY `item_id` (`item_id`),
  KEY `batch_id` (`batch_id`),
  CONSTRAINT `stock_return_wallets_ibfk_1` FOREIGN KEY (`return_id`) REFERENCES `stock_returns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stock_return_wallets_ibfk_2` FOREIGN KEY (`return_item_id`) REFERENCES `stock_return_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stock_return_wallets_ibfk_3` FOREIGN KEY (`target_location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stock_return_wallets_ibfk_4` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `stock_return_wallets_ibfk_5` FOREIGN KEY (`batch_id`) REFERENCES `item_batches` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Table structure for table `stock_returns`
CREATE TABLE `stock_returns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `return_no` varchar(80) NOT NULL,
  `return_type` enum('CLINIC_TO_BRANCH','BRANCH_TO_MAIN','MAIN_TO_VENDOR') NOT NULL,
  `from_location_id` int(11) DEFAULT NULL,
  `to_location_id` int(11) DEFAULT NULL,
  `vendor_id` int(11) DEFAULT NULL,
  `return_reason` enum('EXPIRED','DAMAGED','EXCESS_STOCK','WRONG_ITEM','OTHER') NOT NULL DEFAULT 'EXCESS_STOCK',
  `remarks` text,
  `subtotal` decimal(15,3) NOT NULL DEFAULT '0.000',
  `vat_amount` decimal(15,3) NOT NULL DEFAULT '0.000',
  `total_val` decimal(15,3) NOT NULL DEFAULT '0.000',
  `document_url` varchar(255) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `return_reference` varchar(80) DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `notes` text,
  `status` enum('PENDING_ACCEPTANCE','ACCEPTED','REJECTED','PARTIALLY_ACCEPTED','RESTORED','RESTORED_TO_STOCK') NOT NULL DEFAULT 'PENDING_ACCEPTANCE',
  `action_by` int(11) DEFAULT NULL,
  `action_at` datetime DEFAULT NULL,
  `rejection_reason` text,
  `original_transfer_no` varchar(80) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `return_no` (`return_no`),
  KEY `from_location_id` (`from_location_id`),
  KEY `to_location_id` (`to_location_id`),
  KEY `vendor_id` (`vendor_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `stock_returns_ibfk_1` FOREIGN KEY (`from_location_id`) REFERENCES `locations` (`id`),
  CONSTRAINT `stock_returns_ibfk_2` FOREIGN KEY (`to_location_id`) REFERENCES `locations` (`id`),
  CONSTRAINT `stock_returns_ibfk_3` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`),
  CONSTRAINT `stock_returns_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Table structure for table `stock_transfer_items`
CREATE TABLE `stock_transfer_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transfer_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `batch_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `unit_price` decimal(15,3) NOT NULL DEFAULT '0.000',
  `subtotal` decimal(15,3) NOT NULL DEFAULT '0.000',
  PRIMARY KEY (`id`),
  KEY `transfer_id` (`transfer_id`),
  KEY `item_id` (`item_id`),
  KEY `batch_id` (`batch_id`),
  CONSTRAINT `stock_transfer_items_ibfk_1` FOREIGN KEY (`transfer_id`) REFERENCES `stock_transfers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stock_transfer_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  CONSTRAINT `stock_transfer_items_ibfk_3` FOREIGN KEY (`batch_id`) REFERENCES `item_batches` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Table structure for table `stock_transfers`
CREATE TABLE `stock_transfers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transfer_no` varchar(80) NOT NULL,
  `from_location_id` int(11) NOT NULL,
  `to_location_id` int(11) NOT NULL,
  `transfer_type` enum('BRANCH_INVOICED','CLINIC_TRANSFER') NOT NULL,
  `status` enum('DISPATCHED','RECEIVED','CANCELLED') DEFAULT 'DISPATCHED',
  `invoice_no` varchar(80) DEFAULT NULL,
  `total_val` decimal(15,3) DEFAULT '0.000',
  `subtotal` decimal(15,3) DEFAULT '0.000',
  `vat_amount` decimal(15,3) DEFAULT '0.000',
  `paid_amount` decimal(15,3) DEFAULT '0.000',
  `payment_status` enum('UNPAID','PARTIAL','PAID') DEFAULT 'UNPAID',
  `payment_method` enum('CASH','BANK_TRANSFER','CHEQUE') DEFAULT 'CASH',
  `bank_name` varchar(150) DEFAULT NULL,
  `bank_reference` varchar(100) DEFAULT NULL,
  `cheque_no` varchar(80) DEFAULT NULL,
  `cheque_date` date DEFAULT NULL,
  `remarks` text,
  `created_by` int(11) NOT NULL,
  `received_by` int(11) DEFAULT NULL,
  `dispatched_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `received_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transfer_no` (`transfer_no`),
  KEY `from_location_id` (`from_location_id`),
  KEY `to_location_id` (`to_location_id`),
  KEY `created_by` (`created_by`),
  KEY `received_by` (`received_by`),
  CONSTRAINT `stock_transfers_ibfk_1` FOREIGN KEY (`from_location_id`) REFERENCES `locations` (`id`),
  CONSTRAINT `stock_transfers_ibfk_2` FOREIGN KEY (`to_location_id`) REFERENCES `locations` (`id`),
  CONSTRAINT `stock_transfers_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `stock_transfers_ibfk_4` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Table structure for table `system_audit_trail`
CREATE TABLE `system_audit_trail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` datetime DEFAULT CURRENT_TIMESTAMP,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(80) DEFAULT 'System',
  `role` varchar(50) DEFAULT 'SYSTEM',
  `ip_address` varchar(50) DEFAULT NULL,
  `module` varchar(80) NOT NULL,
  `action` varchar(80) NOT NULL,
  `old_values` longtext,
  `new_values` longtext,
  `location_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_audit_module` (`module`),
  KEY `idx_audit_user` (`user_id`),
  KEY `idx_audit_time` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Table structure for table `system_sequences`
CREATE TABLE `system_sequences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sequence_key` varchar(100) NOT NULL,
  `prefix` varchar(50) NOT NULL,
  `current_val` int(11) NOT NULL DEFAULT '0',
  `padding_length` int(11) NOT NULL DEFAULT '4',
  `format_template` varchar(100) DEFAULT '{PREFIX}{SEQ}',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sequence_key` (`sequence_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Seed data for table `system_sequences`
INSERT INTO `system_sequences` (`id`, `sequence_key`, `prefix`, `current_val`, `padding_length`, `format_template`, `updated_at`) VALUES ('1', 'vendor', 'VND-', '0', '4', '{PREFIX}{SEQ}', '2026-08-13 16:36:03');
INSERT INTO `system_sequences` (`id`, `sequence_key`, `prefix`, `current_val`, `padding_length`, `format_template`, `updated_at`) VALUES ('2', 'branch', 'LOC-SUB-', '0', '4', '{PREFIX}{SEQ}', '2026-08-13 16:36:03');
INSERT INTO `system_sequences` (`id`, `sequence_key`, `prefix`, `current_val`, `padding_length`, `format_template`, `updated_at`) VALUES ('3', 'clinic', 'LOC-CLN-', '0', '4', '{PREFIX}{SEQ}', '2026-08-13 16:36:03');
INSERT INTO `system_sequences` (`id`, `sequence_key`, `prefix`, `current_val`, `padding_length`, `format_template`, `updated_at`) VALUES ('4', 'customer', 'CUST-', '0', '4', '{PREFIX}{SEQ}', '2026-08-13 16:36:03');
INSERT INTO `system_sequences` (`id`, `sequence_key`, `prefix`, `current_val`, `padding_length`, `format_template`, `updated_at`) VALUES ('5', 'item', 'ITM-', '0', '4', '{PREFIX}{SEQ}', '2026-08-13 16:36:03');
INSERT INTO `system_sequences` (`id`, `sequence_key`, `prefix`, `current_val`, `padding_length`, `format_template`, `updated_at`) VALUES ('6', 'purchase_invoice', 'PO-INV/', '3', '5', '{PREFIX}{YEAR}/{SEQ}', '2026-08-14 02:39:19');
INSERT INTO `system_sequences` (`id`, `sequence_key`, `prefix`, `current_val`, `padding_length`, `format_template`, `updated_at`) VALUES ('7', 'branch_transfer', 'BINV/', '4', '5', '{PREFIX}{YEAR}/{SEQ}', '2026-08-14 01:15:34');
INSERT INTO `system_sequences` (`id`, `sequence_key`, `prefix`, `current_val`, `padding_length`, `format_template`, `updated_at`) VALUES ('8', 'sales_invoice', 'SA-INV/', '2', '5', '{PREFIX}{YEAR}/{SEQ}', '2026-08-13 17:25:16');
INSERT INTO `system_sequences` (`id`, `sequence_key`, `prefix`, `current_val`, `padding_length`, `format_template`, `updated_at`) VALUES ('9', 'quotation', 'SA-QTN/', '3', '4', '{PREFIX}{YEAR}/{SEQ}', '2026-08-14 01:14:20');
INSERT INTO `system_sequences` (`id`, `sequence_key`, `prefix`, `current_val`, `padding_length`, `format_template`, `updated_at`) VALUES ('10', 'stock_return', 'RET-', '12', '4', '{PREFIX}{SEQ}', '2026-08-14 02:28:19');
INSERT INTO `system_sequences` (`id`, `sequence_key`, `prefix`, `current_val`, `padding_length`, `format_template`, `updated_at`) VALUES ('11', 'return', 'RET-', '0', '4', '{PREFIX}{SEQ}', '2026-08-13 16:36:03');
INSERT INTO `system_sequences` (`id`, `sequence_key`, `prefix`, `current_val`, `padding_length`, `format_template`, `updated_at`) VALUES ('12', 'credit_note', 'CN-', '3', '4', '{PREFIX}{SEQ}', '2026-08-14 02:50:57');

-- Table structure for table `system_settings`
CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Seed data for table `system_settings`
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES ('1', 'store_name', 'Al RABEEH GROUP OF MEDICALS', '2026-08-12 13:05:46');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES ('2', 'timezone', 'Asia/Bahrain', '2026-08-12 01:20:09');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES ('3', 'currency_code', 'BHD', '2026-08-12 01:43:43');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES ('4', 'currency_symbol', 'BD', '2026-08-12 00:50:11');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES ('5', 'vat_percent', '10.00', '2026-08-12 00:50:11');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES ('6', 'decimal_places', '3', '2026-08-12 00:50:11');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES ('7', 'company_address', 'Central Highway, Manama, Kingdom of Bahrain', '2026-08-12 00:50:11');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES ('8', 'company_phone', '+973 1700 0000', '2026-08-12 00:50:11');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES ('9', 'company_email', 'admin@organization.bh', '2026-08-12 00:50:11');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES ('16', 'date_format', 'DD/MM/YYYY', '2026-08-12 00:59:41');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES ('25', 'vat_calculation_mode', 'TOTAL_BILL', '2026-08-12 14:41:52');
INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES ('114', 'price_tax_type', 'EXCLUSIVE', '2026-08-12 14:39:41');

-- Table structure for table `users`
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(80) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('ADMIN','STORE_MANAGER','OPD_USER','AUDITOR') NOT NULL,
  `location_id` int(11) DEFAULT NULL,
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `location_id` (`location_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Seed data for table `users`
INSERT INTO `users` (`id`, `username`, `full_name`, `email`, `password_hash`, `role`, `location_id`, `status`, `created_at`) VALUES ('1', 'admin', 'System Administrator', 'admin@organization.org', '$2y$10$i.IgOtpr13Y7ke6Envdrk.nIHSKd29SLD7kaAqX924YoWiNp65ye2', 'ADMIN', '1', 'ACTIVE', '2026-08-11 21:04:48');
INSERT INTO `users` (`id`, `username`, `full_name`, `email`, `password_hash`, `role`, `location_id`, `status`, `created_at`) VALUES ('2', 'store_mgr', 'Main Store Manager', 'store.manager@organization.org', '$2y$10$i.IgOtpr13Y7ke6Envdrk.nIHSKd29SLD7kaAqX924YoWiNp65ye2', 'STORE_MANAGER', '1', 'ACTIVE', '2026-08-11 21:04:48');
INSERT INTO `users` (`id`, `username`, `full_name`, `email`, `password_hash`, `role`, `location_id`, `status`, `created_at`) VALUES ('3', 'sub_mgr_north', 'North Branch Manager', 'north.mgr@organization.org', '$2y$10$i.IgOtpr13Y7ke6Envdrk.nIHSKd29SLD7kaAqX924YoWiNp65ye2', 'STORE_MANAGER', '2', 'ACTIVE', '2026-08-11 21:04:48');
INSERT INTO `users` (`id`, `username`, `full_name`, `email`, `password_hash`, `role`, `location_id`, `status`, `created_at`) VALUES ('4', 'clinic_user1', 'City Clinic Pharmacist', 'clinic1@organization.org', '$2y$10$i.IgOtpr13Y7ke6Envdrk.nIHSKd29SLD7kaAqX924YoWiNp65ye2', 'OPD_USER', '4', 'ACTIVE', '2026-08-11 21:04:48');
INSERT INTO `users` (`id`, `username`, `full_name`, `email`, `password_hash`, `role`, `location_id`, `status`, `created_at`) VALUES ('5', 'auditor', 'Senior System Auditor', 'auditor@organization.org', '$2y$10$i.IgOtpr13Y7ke6Envdrk.nIHSKd29SLD7kaAqX924YoWiNp65ye2', 'AUDITOR', NULL, 'ACTIVE', '2026-08-11 21:04:48');
INSERT INTO `users` (`id`, `username`, `full_name`, `email`, `password_hash`, `role`, `location_id`, `status`, `created_at`) VALUES ('7', 'manager_north', 'Ajit Kumar', 'care@sandslab.com', '$2y$10$igKMyslqLRWclaSxJoeyRujZUgJvm2QYdR0/S2uXy.IpXASw8AHkG', 'STORE_MANAGER', '2', 'ACTIVE', '2026-08-12 18:38:50');
INSERT INTO `users` (`id`, `username`, `full_name`, `email`, `password_hash`, `role`, `location_id`, `status`, `created_at`) VALUES ('8', 'manager_central', 'Ancy Varghese Thekkan', 'ancy@sandslab.com', '$2y$10$m4tU.Slz3WIqSgc1ay8r2.wfoqnSFY/Kdc.AUanSmg09ydUan4442', 'STORE_MANAGER', '1', 'ACTIVE', '2026-08-12 19:16:46');
INSERT INTO `users` (`id`, `username`, `full_name`, `email`, `password_hash`, `role`, `location_id`, `status`, `created_at`) VALUES ('9', 'clinic1', 'Clinic User ', 'click@sandslab.com', '$2y$10$SD1iNFlaZWYMeZIc.otURebOPKjnGZ1GQQXaVZKVRQ5GjnHh1Gga.', 'OPD_USER', '5', 'ACTIVE', '2026-08-13 00:20:28');

-- Table structure for table `vendor_quotation_items`
CREATE TABLE `vendor_quotation_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quotation_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `ordered_qty` int(11) NOT NULL,
  `received_qty` int(11) NOT NULL DEFAULT '0',
  `unit_price` decimal(15,3) NOT NULL DEFAULT '0.000',
  `subtotal` decimal(15,3) NOT NULL DEFAULT '0.000',
  PRIMARY KEY (`id`),
  KEY `quotation_id` (`quotation_id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `vendor_quotation_items_ibfk_1` FOREIGN KEY (`quotation_id`) REFERENCES `vendor_quotations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `vendor_quotation_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Table structure for table `vendor_quotations`
CREATE TABLE `vendor_quotations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quotation_no` varchar(100) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL DEFAULT '1',
  `quotation_date` date NOT NULL,
  `expected_delivery_date` date DEFAULT NULL,
  `total_amount` decimal(15,3) NOT NULL DEFAULT '0.000',
  `status` enum('OPEN','PARTIALLY_RECEIVED','CLOSED') NOT NULL DEFAULT 'OPEN',
  `closure_reason` varchar(255) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `quotation_no` (`quotation_no`),
  KEY `vendor_id` (`vendor_id`),
  KEY `location_id` (`location_id`),
  CONSTRAINT `vendor_quotations_ibfk_1` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `vendor_quotations_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Table structure for table `vendors`
CREATE TABLE `vendors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `code` varchar(50) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `address` text,
  `tax_id` varchar(50) DEFAULT NULL,
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Seed data for table `vendors`
INSERT INTO `vendors` (`id`, `name`, `code`, `contact_person`, `phone`, `email`, `address`, `tax_id`, `status`, `created_at`) VALUES ('1', 'MediTech Pharma Supplies', 'VEND-001', 'John Stevenson', '+1 555-111-2222', 'sales@meditech.com', '500 Pharma Way, Industrial Park', 'TAX-8899001', 'ACTIVE', '2026-08-11 21:04:48');
INSERT INTO `vendors` (`id`, `name`, `code`, `contact_person`, `phone`, `email`, `address`, `tax_id`, `status`, `created_at`) VALUES ('2', 'Global BioHealth Logistics', 'VEND-002', 'Sarah Jenkins', '+1 555-333-4444', 'orders@biohealth.com', '75 Logistics Blvd, Port City', 'TAX-8899002', 'ACTIVE', '2026-08-11 21:04:48');
INSERT INTO `vendors` (`id`, `name`, `code`, `contact_person`, `phone`, `email`, `address`, `tax_id`, `status`, `created_at`) VALUES ('3', 'Apex Medical Instruments', 'VEND-003', 'Robert Chen', '+1 555-777-8888', 'contact@apexmedical.com', '320 Precision Drive, Tech Hub', 'TAX-8899003', 'ACTIVE', '2026-08-11 21:04:48');
INSERT INTO `vendors` (`id`, `name`, `code`, `contact_person`, `phone`, `email`, `address`, `tax_id`, `status`, `created_at`) VALUES ('4', 'SaNDS Lab Middle East W.L.L', 'VND-0005', 'Ajit Kumar KV', '9895765626', 'ajit@sandslab.com', '', '4552256633', 'ACTIVE', '2026-08-12 01:43:01');

