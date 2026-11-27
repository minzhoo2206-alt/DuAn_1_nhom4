-- ONLY local test MySQL. Run as local root in phpMyAdmin, never hosting.
USE fourtech_admin_test;
CREATE TABLE IF NOT EXISTS users (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 public_id CHAR(36) NOT NULL UNIQUE, email VARCHAR(254) NOT NULL UNIQUE,
 phone_ciphertext TEXT NULL, phone_lookup_hash BINARY(32) NULL,
 full_name_ciphertext TEXT NULL, password_hash VARCHAR(255) NOT NULL,
 status ENUM('pending_verification','active','locked','disabled') NOT NULL DEFAULT 'pending_verification',
 email_verified_at DATETIME(6) NULL, last_login_at DATETIME(6) NULL,
 failed_login_count INT UNSIGNED NOT NULL DEFAULT 0, locked_until DATETIME(6) NULL,
 created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
 deleted_at DATETIME(6) NULL, created_by BIGINT UNSIGNED NULL, updated_by BIGINT UNSIGNED NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS roles (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, code VARCHAR(80) NOT NULL UNIQUE, name VARCHAR(150) NOT NULL,
 created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
 deleted_at DATETIME(6) NULL, created_by BIGINT UNSIGNED NULL, updated_by BIGINT UNSIGNED NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS user_has_roles (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NOT NULL, role_id BIGINT UNSIGNED NOT NULL,
 created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
 deleted_at DATETIME(6) NULL, created_by BIGINT UNSIGNED NULL, updated_by BIGINT UNSIGNED NULL,
 UNIQUE KEY uq_test_user_role(user_id,role_id), FOREIGN KEY(user_id) REFERENCES users(id), FOREIGN KEY(role_id) REFERENCES roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO roles(code,name) SELECT 'task16_staff','Nhân viên kiểm thử' WHERE NOT EXISTS (SELECT 1 FROM roles WHERE code='task16_staff');
