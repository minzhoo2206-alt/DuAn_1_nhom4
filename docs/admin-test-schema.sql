-- FOURTECH Task 14.3: run ONLY on local MySQL, never on shared hosting.
-- A new, isolated database. No DROP, DELETE or changes to existing schemas.
CREATE DATABASE fourtech_admin_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fourtech_admin_test;
CREATE TABLE brands (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100) NOT NULL, slug VARCHAR(120) NOT NULL UNIQUE,
 is_active BOOLEAN NOT NULL DEFAULT TRUE,
 created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
 updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
 deleted_at DATETIME(6) NULL, created_by BIGINT UNSIGNED NULL, updated_by BIGINT UNSIGNED NULL
) ENGINE=InnoDB;
CREATE TABLE categories LIKE brands;
CREATE TABLE products (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 public_id CHAR(36) NOT NULL UNIQUE,
 brand_id BIGINT UNSIGNED NOT NULL, category_id BIGINT UNSIGNED NOT NULL,
 name VARCHAR(200) NOT NULL, slug VARCHAR(220) NOT NULL UNIQUE,
 description TEXT NULL, specifications JSON NULL,
 warranty_months SMALLINT UNSIGNED NOT NULL DEFAULT 12, warranty_terms TEXT NULL,
 status ENUM('draft','active','hidden') NOT NULL DEFAULT 'draft',
 created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
 updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
 deleted_at DATETIME(6) NULL, created_by BIGINT UNSIGNED NULL, updated_by BIGINT UNSIGNED NULL,
 FOREIGN KEY (brand_id) REFERENCES brands(id),
 FOREIGN KEY (category_id) REFERENCES categories(id)
) ENGINE=InnoDB;
INSERT INTO brands(name,slug) VALUES ('Apple','apple'),('Samsung','samsung'),('OPPO','oppo'),('Xiaomi','xiaomi');
INSERT INTO categories(name,slug) VALUES ('Điện thoại','dien-thoai'),('Điện thoại gaming','dien-thoai-gaming');
