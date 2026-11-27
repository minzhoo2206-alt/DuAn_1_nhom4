-- Local only, run after Task 17. No DROP. Reduced ERD for synthetic COD orders.
USE fourtech_admin_test;
CREATE TABLE IF NOT EXISTS orders (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, public_id CHAR(36) NOT NULL UNIQUE, user_id BIGINT UNSIGNED NOT NULL,
 checkout_key VARCHAR(80) NOT NULL UNIQUE, request_hash BINARY(32) NOT NULL,
 status ENUM('pending_confirmation','confirmed','packing','shipped','delivered','delivery_failed','returning','returned','cancelled','rejected','expired') NOT NULL DEFAULT 'pending_confirmation',
 items_total DECIMAL(15,2) NOT NULL, tax_total DECIMAL(15,2) NOT NULL, shipping_fee DECIMAL(15,2) NOT NULL, cod_total DECIMAL(15,2) NOT NULL, currency VARCHAR(3) NOT NULL DEFAULT 'VND',
 recipient_name_ciphertext TEXT NULL, phone_ciphertext TEXT NULL, address_ciphertext TEXT NULL, reservation_expires_at DATETIME(6) NULL,
 confirmed_at DATETIME(6) NULL, delivered_at DATETIME(6) NULL, cancelled_at DATETIME(6) NULL, cancellation_reason VARCHAR(255) NULL,
 created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), deleted_at DATETIME(6) NULL, created_by BIGINT UNSIGNED NULL, updated_by BIGINT UNSIGNED NULL,
 FOREIGN KEY(user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS order_items (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, order_id BIGINT UNSIGNED NOT NULL, variant_id BIGINT UNSIGNED NULL,
 product_name VARCHAR(200) NOT NULL, sku VARCHAR(80) NOT NULL, color_name VARCHAR(100) NULL, storage_gb SMALLINT UNSIGNED NULL, ram_gb SMALLINT UNSIGNED NULL,
 quantity INT UNSIGNED NOT NULL, unit_price DECIMAL(15,2) NOT NULL, vat_rate DECIMAL(5,2) NOT NULL DEFAULT 0, vat_applicable BOOLEAN NOT NULL DEFAULT FALSE,
 line_tax DECIMAL(15,2) NOT NULL, line_total DECIMAL(15,2) NOT NULL, warranty_months SMALLINT UNSIGNED NOT NULL DEFAULT 12, warranty_terms TEXT NULL,
 created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), deleted_at DATETIME(6) NULL, created_by BIGINT UNSIGNED NULL, updated_by BIGINT UNSIGNED NULL,
 UNIQUE KEY uq_test_order_sku(order_id,sku), FOREIGN KEY(order_id) REFERENCES orders(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS order_status_history (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, order_id BIGINT UNSIGNED NOT NULL, from_status VARCHAR(40) NOT NULL, to_status VARCHAR(40) NOT NULL, reason VARCHAR(255) NULL,
 created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), deleted_at DATETIME(6) NULL, created_by BIGINT UNSIGNED NULL, updated_by BIGINT UNSIGNED NULL,
 FOREIGN KEY(order_id) REFERENCES orders(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
START TRANSACTION;
INSERT INTO permissions(code,name) SELECT 'task18.orders.manage','Quản lý đơn hàng mẫu' WHERE NOT EXISTS(SELECT 1 FROM permissions WHERE code='task18.orders.manage');
INSERT INTO role_has_permissions(role_id,permission_id)
SELECT r.id,p.id FROM roles r CROSS JOIN permissions p WHERE r.code='task17_admin' AND r.deleted_at IS NULL AND p.code='task18.orders.manage' AND p.deleted_at IS NULL AND NOT EXISTS(SELECT 1 FROM role_has_permissions rp WHERE rp.role_id=r.id AND rp.permission_id=p.id);
-- Use an existing local test account only, no new password or real PII.
SET @task18_user = (SELECT MIN(id) FROM users WHERE email LIKE '%@example.test' AND status='active' AND deleted_at IS NULL);
INSERT INTO orders(public_id,user_id,checkout_key,request_hash,status,items_total,tax_total,shipping_fee,cod_total)
SELECT UUID(),@task18_user,'task18-demo-001',UNHEX(SHA2('task18-demo-001',256)),'pending_confirmation',15990000,0,30000,16020000 WHERE @task18_user IS NOT NULL AND NOT EXISTS(SELECT 1 FROM orders WHERE checkout_key='task18-demo-001');
INSERT INTO orders(public_id,user_id,checkout_key,request_hash,status,items_total,tax_total,shipping_fee,cod_total)
SELECT UUID(),@task18_user,'task18-demo-002',UNHEX(SHA2('task18-demo-002',256)),'pending_confirmation',8990000,0,30000,9020000 WHERE @task18_user IS NOT NULL AND NOT EXISTS(SELECT 1 FROM orders WHERE checkout_key='task18-demo-002');
INSERT INTO order_items(order_id,product_name,sku,quantity,unit_price,line_tax,line_total)
SELECT o.id,'iPhone mẫu FOURTECH','TASK18-IPHONE',1,15990000,0,15990000 FROM orders o WHERE o.checkout_key='task18-demo-001' AND NOT EXISTS(SELECT 1 FROM order_items i WHERE i.order_id=o.id AND i.sku='TASK18-IPHONE');
INSERT INTO order_items(order_id,product_name,sku,quantity,unit_price,line_tax,line_total)
SELECT o.id,'Samsung mẫu FOURTECH','TASK18-SAMSUNG',1,8990000,0,8990000 FROM orders o WHERE o.checkout_key='task18-demo-002' AND NOT EXISTS(SELECT 1 FROM order_items i WHERE i.order_id=o.id AND i.sku='TASK18-SAMSUNG');
COMMIT;
SELECT id,checkout_key,status FROM orders WHERE checkout_key LIKE 'task18-demo-%';
