-- Read only. Run in local phpMyAdmin after --apply.
SELECT COUNT(*) AS demo_products FROM fourtech_admin_test.products WHERE slug LIKE 'task19-phone-%' AND deleted_at IS NULL;
SELECT COUNT(*) AS demo_customers FROM fourtech_admin_test.users WHERE email LIKE 'task19-customer-%@example.test' AND deleted_at IS NULL;
SELECT checkout_key,status FROM fourtech_admin_test.orders WHERE checkout_key LIKE 'task19-demo-%' AND deleted_at IS NULL ORDER BY checkout_key;
SELECT COUNT(*) AS active_categories FROM fourtech_admin_test.categories WHERE is_active=1 AND deleted_at IS NULL;
SELECT COUNT(*) AS active_brands FROM fourtech_admin_test.brands WHERE is_active=1 AND deleted_at IS NULL;
