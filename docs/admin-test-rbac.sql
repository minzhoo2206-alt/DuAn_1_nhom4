-- Local only. First import admin-test-users.sql. Never run on hosting.
USE fourtech_admin_test;
CREATE TABLE IF NOT EXISTS permissions (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 code VARCHAR(80) NOT NULL UNIQUE, name VARCHAR(150) NOT NULL,
 created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
 deleted_at DATETIME(6) NULL, created_by BIGINT UNSIGNED NULL, updated_by BIGINT UNSIGNED NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS role_has_permissions (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, role_id BIGINT UNSIGNED NOT NULL, permission_id BIGINT UNSIGNED NOT NULL,
 created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
 deleted_at DATETIME(6) NULL, created_by BIGINT UNSIGNED NULL, updated_by BIGINT UNSIGNED NULL,
 UNIQUE KEY uq_test_role_permission(role_id,permission_id), FOREIGN KEY(role_id) REFERENCES roles(id), FOREIGN KEY(permission_id) REFERENCES permissions(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO roles(code,name) SELECT 'task17_admin','Admin kiểm thử' WHERE NOT EXISTS (SELECT 1 FROM roles WHERE code='task17_admin');
INSERT INTO roles(code,name) SELECT 'task17_customer','Khách hàng kiểm thử' WHERE NOT EXISTS (SELECT 1 FROM roles WHERE code='task17_customer');
INSERT INTO permissions(code,name) SELECT 'task17.catalog.read','Xem sản phẩm, danh mục, thương hiệu' WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE code='task17.catalog.read');
INSERT INTO permissions(code,name) SELECT 'task17.catalog.write','Thay đổi sản phẩm, danh mục, thương hiệu' WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE code='task17.catalog.write');
INSERT INTO permissions(code,name) SELECT 'task17.users.manage','Quản lý tài khoản' WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE code='task17.users.manage');
INSERT INTO permissions(code,name) SELECT 'task17.rbac.manage','Quản lý phân quyền' WHERE NOT EXISTS (SELECT 1 FROM permissions WHERE code='task17.rbac.manage');
INSERT INTO role_has_permissions(role_id,permission_id)
SELECT r.id,p.id FROM roles r CROSS JOIN permissions p
WHERE r.deleted_at IS NULL AND p.deleted_at IS NULL AND ((r.code='task17_admin' AND p.code IN ('task17.catalog.read','task17.catalog.write','task17.users.manage','task17.rbac.manage')) OR (r.code='task16_staff' AND p.code='task17.catalog.read'))
AND NOT EXISTS (SELECT 1 FROM role_has_permissions rp WHERE rp.role_id=r.id AND rp.permission_id=p.id);
