-- Run ONCE as local root, after creating admin17@example.test through Task 16.
-- Only an active test account with exactly one active staff assignment can become admin.
USE fourtech_admin_test;
START TRANSACTION;
SET @uid := NULL;
SET @admin_role := NULL;
SELECT id INTO @admin_role FROM roles WHERE code='task17_admin' AND deleted_at IS NULL FOR UPDATE;
SELECT u.id INTO @uid FROM users u WHERE u.email='admin17@example.test' AND u.status='active' AND u.deleted_at IS NULL
AND @admin_role IS NOT NULL AND (SELECT COUNT(*) FROM user_has_roles ur WHERE ur.user_id=u.id AND ur.deleted_at IS NULL)=1
AND EXISTS (SELECT 1 FROM user_has_roles ur JOIN roles r ON r.id=ur.role_id WHERE ur.user_id=u.id AND ur.deleted_at IS NULL AND r.code='task16_staff' AND r.deleted_at IS NULL)
AND NOT EXISTS (SELECT 1 FROM user_has_roles ur WHERE ur.role_id=@admin_role AND ur.deleted_at IS NULL) FOR UPDATE;
UPDATE user_has_roles SET deleted_at=NOW(6),updated_at=NOW(6) WHERE user_id=@uid AND deleted_at IS NULL;
INSERT INTO user_has_roles(user_id,role_id) SELECT @uid,@admin_role WHERE @uid IS NOT NULL AND @admin_role IS NOT NULL
ON DUPLICATE KEY UPDATE deleted_at=NULL,updated_at=NOW(6);
COMMIT;
SELECT u.email,r.code FROM users u JOIN user_has_roles ur ON ur.user_id=u.id JOIN roles r ON r.id=ur.role_id WHERE u.email='admin17@example.test' AND ur.deleted_at IS NULL AND r.deleted_at IS NULL;
