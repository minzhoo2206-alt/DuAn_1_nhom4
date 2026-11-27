-- ONLY local fourtech_admin_test. Additive, no existing rows changed.
USE fourtech_admin_test;
CREATE TABLE IF NOT EXISTS colors (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL, slug VARCHAR(120) NOT NULL UNIQUE,
 is_active BOOLEAN NOT NULL DEFAULT TRUE,
 created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
 deleted_at DATETIME(6) NULL, created_by BIGINT UNSIGNED NULL, updated_by BIGINT UNSIGNED NULL
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS product_configurations (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, public_id CHAR(36) NOT NULL UNIQUE, product_id BIGINT UNSIGNED NOT NULL,
 storage_gb SMALLINT UNSIGNED NOT NULL, ram_gb SMALLINT UNSIGNED NOT NULL,
 list_price DECIMAL(15,2) NOT NULL, sale_price DECIMAL(15,2) NULL,
 sale_starts_at DATETIME(6) NULL, sale_ends_at DATETIME(6) NULL,
 vat_rate DECIMAL(5,2) NOT NULL DEFAULT 0, vat_applicable BOOLEAN NOT NULL DEFAULT FALSE, is_active BOOLEAN NOT NULL DEFAULT TRUE,
 created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
 deleted_at DATETIME(6) NULL, created_by BIGINT UNSIGNED NULL, updated_by BIGINT UNSIGNED NULL,
 UNIQUE KEY uq_task22_config(product_id,storage_gb,ram_gb), FOREIGN KEY(product_id) REFERENCES products(id)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS product_variants (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, public_id CHAR(36) NOT NULL UNIQUE,
 configuration_id BIGINT UNSIGNED NOT NULL, color_id BIGINT UNSIGNED NOT NULL, sku VARCHAR(80) NOT NULL UNIQUE,
 is_active BOOLEAN NOT NULL DEFAULT TRUE,
 created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
 deleted_at DATETIME(6) NULL, created_by BIGINT UNSIGNED NULL, updated_by BIGINT UNSIGNED NULL,
 UNIQUE KEY uq_task22_variant(configuration_id,color_id), FOREIGN KEY(configuration_id) REFERENCES product_configurations(id), FOREIGN KEY(color_id) REFERENCES colors(id)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS product_images (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, product_id BIGINT UNSIGNED NOT NULL, storage_key VARCHAR(400) NOT NULL UNIQUE,
 alt_text VARCHAR(255) NULL, sort_order INT UNSIGNED NOT NULL DEFAULT 0,
 created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
 deleted_at DATETIME(6) NULL, created_by BIGINT UNSIGNED NULL, updated_by BIGINT UNSIGNED NULL,
 FOREIGN KEY(product_id) REFERENCES products(id)
) ENGINE=InnoDB;
INSERT INTO colors(name,slug) VALUES ('Đen','black'),('Trắng','white'),('Xanh','blue'),('Bạc','silver'),('Vàng','gold'),('Tím','purple'),('Titan tự nhiên','natural-titanium'),('Titan đen','black-titanium') ON DUPLICATE KEY UPDATE slug=colors.slug;
