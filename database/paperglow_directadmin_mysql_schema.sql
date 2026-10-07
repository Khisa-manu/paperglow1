-- ============================================================================
-- PAPERGLOW PLATFORM — DIRECTADMIN MYSQL / MARIADB PRODUCTION SCHEMA
-- Complete Multi-Tenant Database Schema with Applications & Modules
-- Compatible with MySQL 8.0+ and MariaDB 10.5+
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------------------------------
-- CORE PLATFORM TABLES
-- ----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `currencies` (
  `code` CHAR(3) NOT NULL,
  `name` VARCHAR(50) NOT NULL,
  `symbol` VARCHAR(10) NOT NULL,
  `decimal_precision` TINYINT UNSIGNED NOT NULL DEFAULT 2,
  `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
  `is_default` BOOLEAN NOT NULL DEFAULT FALSE,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `currencies` (`code`, `name`, `symbol`, `decimal_precision`, `is_active`, `is_default`) VALUES
('KES', 'Kenyan Shilling', 'KSh', 2, 1, 1),
('USD', 'US Dollar', '$', 2, 1, 0),
('EUR', 'Euro', '€', 2, 1, 0),
('GBP', 'British Pound', '£', 2, 1, 0);

CREATE TABLE IF NOT EXISTS `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` CHAR(36) NOT NULL UNIQUE,
  `name` VARCHAR(191) NOT NULL,
  `email` VARCHAR(191) NOT NULL UNIQUE,
  `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(50) NULL DEFAULT NULL,
  `avatar_url` VARCHAR(2048) NULL DEFAULT NULL,
  `two_factor_enabled` BOOLEAN NOT NULL DEFAULT FALSE,
  `status` ENUM('active', 'suspended', 'pending_verification') NOT NULL DEFAULT 'active',
  `reset_token` VARCHAR(100) NULL DEFAULT NULL,
  `reset_token_expires_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_users_email` (`email`),
  INDEX `idx_users_uuid` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `organizations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` CHAR(36) NOT NULL UNIQUE,
  `name` VARCHAR(191) NOT NULL,
  `slug` VARCHAR(191) NOT NULL UNIQUE,
  `billing_email` VARCHAR(191) NOT NULL,
  `phone` VARCHAR(50) NULL DEFAULT NULL,
  `tax_id` VARCHAR(100) NULL DEFAULT NULL,     -- e.g. KRA PIN
  `address_line1` VARCHAR(255) NULL DEFAULT NULL,
  `city` VARCHAR(100) NULL DEFAULT 'Nairobi',
  `county_state` VARCHAR(100) NULL DEFAULT 'Nairobi County',
  `country_code` CHAR(2) NOT NULL DEFAULT 'KE',
  `preferred_currency` CHAR(3) NOT NULL DEFAULT 'KES',
  `status` ENUM('active', 'past_due', 'suspended') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`preferred_currency`) REFERENCES `currencies` (`code`),
  INDEX `idx_orgs_slug` (`slug`),
  INDEX `idx_orgs_uuid` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `roles` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `organization_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `name` VARCHAR(50) NOT NULL,
  `display_name` VARCHAR(100) NOT NULL,
  `description` VARCHAR(255) NULL DEFAULT NULL,
  `is_system` BOOLEAN NOT NULL DEFAULT FALSE,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_roles_org` (`organization_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `permissions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `module` VARCHAR(50) NOT NULL,
  `description` VARCHAR(255) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_permissions_module` (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `role_permissions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_id` BIGINT UNSIGNED NOT NULL,
  `permission_id` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_role_perm` (`role_id`, `permission_id`),
  FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `organization_members` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `organization_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `role_id` BIGINT UNSIGNED NOT NULL,
  `status` ENUM('active', 'invited', 'suspended') NOT NULL DEFAULT 'active',
  `joined_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_org_user` (`organization_id`, `user_id`),
  FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `applications` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `name` VARCHAR(191) NOT NULL,
  `tagline` VARCHAR(255) NOT NULL,
  `category` VARCHAR(100) NOT NULL,
  `icon_name` VARCHAR(50) NOT NULL,
  `monthly_price_kes` INT UNSIGNED NOT NULL DEFAULT 3800,
  `annual_price_kes` INT UNSIGNED NOT NULL DEFAULT 38000,
  `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `subscriptions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` CHAR(36) NOT NULL UNIQUE,
  `organization_id` BIGINT UNSIGNED NOT NULL,
  `app_slug` VARCHAR(100) NOT NULL,
  `plan_slug` VARCHAR(50) NOT NULL DEFAULT 'professional',
  `billing_cadence` ENUM('monthly', 'annual') NOT NULL DEFAULT 'monthly',
  `status` ENUM('active', 'trialing', 'past_due', 'canceled') NOT NULL DEFAULT 'active',
  `price_kes` INT UNSIGNED NOT NULL DEFAULT 3800,
  `current_period_start` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `current_period_end` TIMESTAMP NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  INDEX `idx_subs_org_app` (`organization_id`, `app_slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `organization_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `category` ENUM('booking', 'chama', 'loan', 'property', 'clinic', 'school', 'legal', 'ticketing', 'business', 'inventory', 'system') NOT NULL DEFAULT 'system',
  `type` VARCHAR(100) NOT NULL,
  `link` VARCHAR(255) NULL DEFAULT NULL,
  `is_read` BOOLEAN NOT NULL DEFAULT FALSE,
  `scheduled_for` TIMESTAMP NULL DEFAULT NULL,
  `channel` ENUM('in_app', 'sms', 'email', 'whatsapp') NOT NULL DEFAULT 'in_app',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  INDEX `idx_notif_org_read` (`organization_id`, `is_read`),
  INDEX `idx_notif_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `organization_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL,
  `entity_type` VARCHAR(100) NOT NULL,
  `entity_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` VARCHAR(255) NULL DEFAULT NULL,
  `details` TEXT NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_audit_org` (`organization_id`),
  INDEX `idx_audit_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `documents` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` CHAR(36) NOT NULL UNIQUE,
  `organization_id` BIGINT UNSIGNED NOT NULL,
  `uploaded_by_user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `category` ENUM('invoice', 'receipt', 'membership_certificate', 'digital_id_card', 'legal_doc', 'clinic_doc', 'school_report', 'artwork', 'other') NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `file_name` VARCHAR(255) NOT NULL,
  `original_name` VARCHAR(255) NOT NULL,
  `mime_type` VARCHAR(100) NOT NULL,
  `file_size_bytes` BIGINT UNSIGNED NOT NULL,
  `storage_path` VARCHAR(512) NOT NULL,
  `entity_type` VARCHAR(100) NULL DEFAULT NULL,
  `entity_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `metadata_json` JSON NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  INDEX `idx_docs_org_cat` (`organization_id`, `category`),
  INDEX `idx_docs_entity` (`entity_type`, `entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- COMMERCE & BILLING
-- ----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `orders` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` CHAR(36) NOT NULL UNIQUE,
  `order_number` VARCHAR(50) NOT NULL UNIQUE,
  `organization_id` BIGINT UNSIGNED NOT NULL,
  `created_by_user_id` BIGINT UNSIGNED NOT NULL,
  `order_type` ENUM('software_subscription', 'merchandise') NOT NULL,
  `status` ENUM('pending_payment', 'processing', 'proofing', 'in_production', 'shipped', 'delivered', 'canceled') NOT NULL DEFAULT 'processing',
  `subtotal_kes` BIGINT UNSIGNED NOT NULL,
  `total_kes` BIGINT UNSIGNED NOT NULL,
  `currency_code` CHAR(3) NOT NULL DEFAULT 'KES',
  `item_title` VARCHAR(255) NOT NULL,
  `specs` TEXT NOT NULL,
  `quantity` INT UNSIGNED NOT NULL DEFAULT 1,
  `tracking_number` VARCHAR(100) NULL DEFAULT NULL,
  `estimated_delivery` VARCHAR(100) NULL DEFAULT NULL,
  `customer_notes` TEXT NULL DEFAULT NULL,
  `artwork_approved` BOOLEAN NOT NULL DEFAULT FALSE,
  `proof_version` INT UNSIGNED NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `invoices` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` CHAR(36) NOT NULL UNIQUE,
  `invoice_number` VARCHAR(50) NOT NULL UNIQUE,
  `organization_id` BIGINT UNSIGNED NOT NULL,
  `order_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `amount_kes` BIGINT UNSIGNED NOT NULL,
  `currency_code` CHAR(3) NOT NULL DEFAULT 'KES',
  `status` ENUM('paid', 'open', 'void', 'draft') NOT NULL DEFAULT 'open',
  `due_date` DATE NOT NULL,
  `paid_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `payments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` CHAR(36) NOT NULL UNIQUE,
  `invoice_id` BIGINT UNSIGNED NOT NULL,
  `organization_id` BIGINT UNSIGNED NOT NULL,
  `payment_channel` ENUM('mobile_money', 'card', 'bank_transfer') NOT NULL,
  `provider_name` VARCHAR(50) NOT NULL,
  `amount_kes` BIGINT UNSIGNED NOT NULL,
  `currency_code` CHAR(3) NOT NULL DEFAULT 'KES',
  `provider_reference` VARCHAR(191) NULL DEFAULT NULL,
  `merchant_reference` VARCHAR(191) NOT NULL,
  `customer_msisdn` VARCHAR(30) NULL DEFAULT NULL,
  `status` ENUM('completed', 'pending', 'failed') NOT NULL DEFAULT 'completed',
  `completed_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- APPLICATION TABLES (BUSINESS, PROPERTY, PHARMACY, TICKETING, BOOKING,
-- INVENTORY, LEGAL, SCHOOL, CHAMA, CLINIC)
-- ----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `bm_customers` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` CHAR(36) NOT NULL UNIQUE,
  `organization_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(191) NOT NULL,
  `email` VARCHAR(191) NULL DEFAULT NULL,
  `phone` VARCHAR(50) NULL DEFAULT NULL,
  `address` VARCHAR(255) NULL DEFAULT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `notes` TEXT NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  INDEX `idx_bm_cust_org` (`organization_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `bm_products` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` CHAR(36) NOT NULL UNIQUE,
  `organization_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(191) NOT NULL,
  `sku` VARCHAR(100) NULL DEFAULT NULL,
  `category` VARCHAR(100) NOT NULL,
  `unit_price` DECIMAL(12, 2) NOT NULL,
  `stock_quantity` INT NOT NULL DEFAULT 0,
  `min_alert_stock` INT NOT NULL DEFAULT 5,
  `status` ENUM('in_stock', 'low_stock', 'out_of_stock') NOT NULL DEFAULT 'in_stock',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `bm_invoices` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` CHAR(36) NOT NULL UNIQUE,
  `organization_id` BIGINT UNSIGNED NOT NULL,
  `customer_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `customer_name` VARCHAR(191) NOT NULL,
  `invoice_number` VARCHAR(50) NOT NULL,
  `amount` DECIMAL(12, 2) NOT NULL,
  `status` ENUM('paid', 'pending', 'overdue') NOT NULL DEFAULT 'pending',
  `due_date` DATE NOT NULL,
  `items_json` JSON NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `bm_expenses` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` CHAR(36) NOT NULL UNIQUE,
  `organization_id` BIGINT UNSIGNED NOT NULL,
  `title` VARCHAR(191) NOT NULL,
  `category` VARCHAR(100) NOT NULL,
  `amount` DECIMAL(12, 2) NOT NULL,
  `date` DATE NOT NULL,
  `paid_to` VARCHAR(191) NOT NULL,
  `payment_method` VARCHAR(50) NOT NULL DEFAULT 'M-Pesa',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `bm_employees` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` CHAR(36) NOT NULL UNIQUE,
  `organization_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(191) NOT NULL,
  `role` VARCHAR(100) NOT NULL,
  `department` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(50) NOT NULL,
  `email` VARCHAR(191) NOT NULL,
  `salary` DECIMAL(12, 2) NOT NULL,
  `status` ENUM('active', 'on_leave', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `bm_orders` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` CHAR(36) NOT NULL UNIQUE,
  `organization_id` BIGINT UNSIGNED NOT NULL,
  `order_number` VARCHAR(50) NOT NULL,
  `customer_name` VARCHAR(191) NOT NULL,
  `items_count` INT NOT NULL DEFAULT 1,
  `total_amount` DECIMAL(12, 2) NOT NULL,
  `status` ENUM('pending', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
  `date` DATE NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `bm_appointments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` CHAR(36) NOT NULL UNIQUE,
  `organization_id` BIGINT UNSIGNED NOT NULL,
  `client_name` VARCHAR(191) NOT NULL,
  `service` VARCHAR(191) NOT NULL,
  `date` DATE NOT NULL,
  `time` VARCHAR(20) NOT NULL,
  `status` ENUM('confirmed', 'pending', 'cancelled') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `bm_payments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` CHAR(36) NOT NULL UNIQUE,
  `organization_id` BIGINT UNSIGNED NOT NULL,
  `reference` VARCHAR(100) NOT NULL,
  `customer_name` VARCHAR(191) NOT NULL,
  `amount` DECIMAL(12, 2) NOT NULL,
  `channel` VARCHAR(50) NOT NULL DEFAULT 'M-Pesa',
  `date` DATE NOT NULL,
  `status` ENUM('completed', 'pending') NOT NULL DEFAULT 'completed',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pm_properties` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` CHAR(36) NOT NULL UNIQUE,
  `organization_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(191) NOT NULL,
  `type` VARCHAR(50) NOT NULL DEFAULT 'Apartment Complex',
  `location` VARCHAR(191) NOT NULL,
  `units_count` INT NOT NULL DEFAULT 1,
  `occupancy_rate` INT NOT NULL DEFAULT 100,
  `monthly_revenue` DECIMAL(12, 2) NOT NULL DEFAULT 0,
  `status` ENUM('Active', 'Maintenance', 'Full') NOT NULL DEFAULT 'Active',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pm_tenants` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` CHAR(36) NOT NULL UNIQUE,
  `organization_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(191) NOT NULL,
  `phone` VARCHAR(50) NOT NULL,
  `email` VARCHAR(191) NOT NULL,
  `property_name` VARCHAR(191) NOT NULL,
  `unit` VARCHAR(50) NOT NULL,
  `rent_amount` DECIMAL(12, 2) NOT NULL,
  `deposit_paid` DECIMAL(12, 2) NOT NULL,
  `lease_start` DATE NOT NULL,
  `status` ENUM('Active', 'Notice Given', 'Overdue') NOT NULL DEFAULT 'Active',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pm_rent_payments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` CHAR(36) NOT NULL UNIQUE,
  `organization_id` BIGINT UNSIGNED NOT NULL,
  `tenant_name` VARCHAR(191) NOT NULL,
  `property_name` VARCHAR(191) NOT NULL,
  `unit` VARCHAR(50) NOT NULL,
  `amount` DECIMAL(12, 2) NOT NULL,
  `date` DATE NOT NULL,
  `channel` VARCHAR(50) NOT NULL DEFAULT 'M-Pesa',
  `reference` VARCHAR(100) NOT NULL,
  `status` ENUM('Completed', 'Pending', 'Failed') NOT NULL DEFAULT 'Completed',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pm_maintenance` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` CHAR(36) NOT NULL UNIQUE,
  `organization_id` BIGINT UNSIGNED NOT NULL,
  `property_name` VARCHAR(191) NOT NULL,
  `unit` VARCHAR(50) NOT NULL,
  `issue` VARCHAR(255) NOT NULL,
  `priority` ENUM('High', 'Medium', 'Low') NOT NULL DEFAULT 'Medium',
  `status` ENUM('Open', 'In Progress', 'Resolved') NOT NULL DEFAULT 'Open',
  `assigned_to` VARCHAR(191) NOT NULL,
  `cost` DECIMAL(12, 2) NOT NULL DEFAULT 0,
  `reported_date` DATE NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pharm_medicines` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` CHAR(36) NOT NULL UNIQUE,
  `organization_id` BIGINT UNSIGNED NOT NULL,
  `brand_name` VARCHAR(191) NOT NULL,
  `generic_name` VARCHAR(191) NOT NULL,
  `category` VARCHAR(100) NOT NULL,
  `batch_number` VARCHAR(100) NOT NULL,
  `expiry_date` DATE NOT NULL,
  `stock_quantity` INT NOT NULL DEFAULT 0,
  `unit_price` DECIMAL(12, 2) NOT NULL,
  `reorder_level` INT NOT NULL DEFAULT 20,
  `status` ENUM('in_stock', 'low_stock', 'expired') NOT NULL DEFAULT 'in_stock',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pharm_sales` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` CHAR(36) NOT NULL UNIQUE,
  `organization_id` BIGINT UNSIGNED NOT NULL,
  `receipt_number` VARCHAR(100) NOT NULL,
  `customer_name` VARCHAR(191) NOT NULL DEFAULT 'Walk-in Customer',
  `total_amount` DECIMAL(12, 2) NOT NULL,
  `payment_method` VARCHAR(50) NOT NULL DEFAULT 'M-Pesa',
  `items_json` JSON NULL DEFAULT NULL,
  `date` DATE NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tickets` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` CHAR(36) NOT NULL UNIQUE,
  `organization_id` BIGINT UNSIGNED NOT NULL,
  `ticket_code` VARCHAR(50) NOT NULL,
  `customer_name` VARCHAR(191) NOT NULL,
  `customer_email` VARCHAR(191) NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `category` VARCHAR(100) NOT NULL,
  `priority` ENUM('Low', 'Medium', 'High', 'Urgent') NOT NULL DEFAULT 'Medium',
  `status` ENUM('Open', 'In Progress', 'Waiting on Customer', 'Resolved', 'Closed') NOT NULL DEFAULT 'Open',
  `assigned_to` VARCHAR(191) NOT NULL DEFAULT 'Unassigned',
  `sla_due` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `bookings` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` CHAR(36) NOT NULL UNIQUE,
  `organization_id` BIGINT UNSIGNED NOT NULL,
  `customer_name` VARCHAR(191) NOT NULL,
  `customer_phone` VARCHAR(50) NOT NULL,
  `customer_email` VARCHAR(191) NOT NULL,
  `service_name` VARCHAR(191) NOT NULL,
  `staff_name` VARCHAR(191) NOT NULL,
  `booking_date` DATE NOT NULL,
  `booking_time` VARCHAR(20) NOT NULL,
  `duration_minutes` INT NOT NULL DEFAULT 60,
  `total_price` DECIMAL(12, 2) NOT NULL,
  `status` ENUM('Confirmed', 'Pending', 'Cancelled', 'Completed') NOT NULL DEFAULT 'Pending',
  `reminder_sent` BOOLEAN NOT NULL DEFAULT FALSE,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `inventory_products` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` CHAR(36) NOT NULL UNIQUE,
  `organization_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(191) NOT NULL,
  `sku` VARCHAR(100) NOT NULL,
  `barcode` VARCHAR(100) NULL DEFAULT NULL,
  `category` VARCHAR(100) NOT NULL,
  `buying_price` DECIMAL(12, 2) NOT NULL,
  `selling_price` DECIMAL(12, 2) NOT NULL,
  `quantity` INT NOT NULL DEFAULT 0,
  `min_stock` INT NOT NULL DEFAULT 10,
  `status` ENUM('in_stock', 'low_stock', 'out_of_stock') NOT NULL DEFAULT 'in_stock',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `legal_matters` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` CHAR(36) NOT NULL UNIQUE,
  `organization_id` BIGINT UNSIGNED NOT NULL,
  `matter_number` VARCHAR(100) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `client_name` VARCHAR(191) NOT NULL,
  `category` VARCHAR(100) NOT NULL,
  `status` ENUM('Active', 'Pending Court', 'In Review', 'Concluded') NOT NULL DEFAULT 'Active',
  `court_station` VARCHAR(191) NOT NULL,
  `case_number` VARCHAR(100) NOT NULL,
  `lead_advocate` VARCHAR(191) NOT NULL,
  `next_date` DATE NOT NULL,
  `billed_amount` DECIMAL(12, 2) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `school_students` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` CHAR(36) NOT NULL UNIQUE,
  `organization_id` BIGINT UNSIGNED NOT NULL,
  `admission_number` VARCHAR(50) NOT NULL,
  `name` VARCHAR(191) NOT NULL,
  `class_grade` VARCHAR(100) NOT NULL,
  `gender` ENUM('Male', 'Female') NOT NULL,
  `dob` DATE NOT NULL,
  `guardian_name` VARCHAR(191) NOT NULL,
  `guardian_phone` VARCHAR(50) NOT NULL,
  `fee_balance` DECIMAL(12, 2) NOT NULL DEFAULT 0,
  `status` ENUM('Active', 'Transferred', 'Graduated') NOT NULL DEFAULT 'Active',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `chama_members` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` CHAR(36) NOT NULL UNIQUE,
  `organization_id` BIGINT UNSIGNED NOT NULL,
  `member_number` VARCHAR(50) NOT NULL,
  `name` VARCHAR(191) NOT NULL,
  `phone` VARCHAR(50) NOT NULL,
  `email` VARCHAR(191) NOT NULL,
  `national_id` VARCHAR(50) NOT NULL,
  `kra_pin` VARCHAR(50) NOT NULL,
  `role` VARCHAR(50) NOT NULL DEFAULT 'Member',
  `status` ENUM('Active', 'Dormant', 'Suspended') NOT NULL DEFAULT 'Active',
  `total_contributions` DECIMAL(12, 2) NOT NULL DEFAULT 0,
  `active_loan_balance` DECIMAL(12, 2) NOT NULL DEFAULT 0,
  `shares_units` INT NOT NULL DEFAULT 10,
  `joined_date` DATE NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `clinic_patients` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` CHAR(36) NOT NULL UNIQUE,
  `organization_id` BIGINT UNSIGNED NOT NULL,
  `opd_number` VARCHAR(50) NOT NULL,
  `name` VARCHAR(191) NOT NULL,
  `phone` VARCHAR(50) NOT NULL,
  `email` VARCHAR(191) NOT NULL,
  `gender` ENUM('Male', 'Female') NOT NULL,
  `dob` DATE NOT NULL,
  `blood_group` VARCHAR(10) NOT NULL DEFAULT 'O+',
  `allergies` VARCHAR(255) NOT NULL DEFAULT 'None',
  `chronic_conditions` VARCHAR(255) NOT NULL DEFAULT 'None',
  `total_visits` INT NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
