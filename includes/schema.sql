CREATE DATABASE IF NOT EXISTS plate_scan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE plate_scan;

CREATE TABLE IF NOT EXISTS vehicles (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  plate_number VARCHAR(20) NOT NULL,
  owner_name VARCHAR(100) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  make VARCHAR(50) NOT NULL,
  model VARCHAR(50) NOT NULL,
  color VARCHAR(30) NOT NULL,
  body_type VARCHAR(30) NOT NULL,
  base_fee DECIMAL(10,2) DEFAULT 50.00,
  currency VARCHAR(3) DEFAULT 'USD',
  fee_converted DECIMAL(10,2) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_plate (plate_number),
  KEY idx_owner (owner_name),
  KEY idx_type (body_type),
  KEY idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS rate_cache (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  base_currency VARCHAR(3) NOT NULL,
  rates_json TEXT NOT NULL,
  fetched_at INT UNSIGNED NOT NULL,
  KEY idx_base_time (base_currency, fetched_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS rate_limits (
  ip_address VARCHAR(45) NOT NULL,
  endpoint VARCHAR(50) NOT NULL,
  request_count INT UNSIGNED DEFAULT 1,
  last_request INT UNSIGNED NOT NULL,
  PRIMARY KEY (ip_address, endpoint),
  KEY idx_ip (ip_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
