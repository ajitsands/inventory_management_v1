-- SQL Database Schema for Licensing Server (key.sandslab.com)
-- Database: applicationkey (local) or sandsl23_key_db (production)

CREATE TABLE IF NOT EXISTS `licensing_admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `licenses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `license_key` VARCHAR(100) NOT NULL UNIQUE,
  `customer_name` VARCHAR(100) NOT NULL,
  `application_name` VARCHAR(100) NOT NULL,
  `domain_name` VARCHAR(100) NOT NULL,
  `ip_address` VARCHAR(50) DEFAULT NULL,
  `private_key` TEXT NOT NULL,
  `public_key` TEXT NOT NULL,
  `expiry_date` DATE DEFAULT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed default admin login (admin / password123)
INSERT INTO `licensing_admins` (`username`, `password`, `status`) 
VALUES ('admin', '$2y$10$2jdPlxOB7JAkjI9bUynEH.0X8pBYJ9U.65ENZbIKreuWiCLYefxxm', 'active')
ON DUPLICATE KEY UPDATE `username`=`username`;
