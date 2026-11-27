# Task 15 – Brand management
Local preview: `php -S 127.0.0.1:8001 -t public docs/admin-db-test.php`. Use only `fourtech_admin_test`. Existing schema and credentials remain unchanged. Run `php docs/check-admin-brand-rules.php` for offline smoke tests.

For Minh: register these routes only behind authenticated Admin RBAC and the team's CSRF middleware, using `App\Controllers\Admin\BrandAdminController`:

| Method | Route | Handler |
|---|---|---|
| GET | /admin/brands | index |
| GET | /admin/brands/create | create |
| POST | /admin/brands | store |
| GET | /admin/brands/{id}/edit | edit |
| POST | /admin/brands/{id}/update | update |
| POST | /admin/brands/{id}/activate | activate |
| POST | /admin/brands/{id}/deactivate | deactivate |
| POST | /admin/brands/{id}/delete | softDelete |

Enable `brandModuleEnabled` in admin layout data once routes are integrated. No core files/routes changed by this patch. The `brand_token` is a module-level token, not a replacement for team auth or middleware; coordinate token conventions. The controller is based on existing CategoryAdminController API and needs real Core integration testing. Confirm official DDL and audit `created_by`/`updated_by` requirements. Local SQL guards check current linked non-deleted products; concurrent product writes and brand lifecycle require shared locking/transaction protocol before production. Slugs remain reserved after soft deletion (unique index). No hard delete. Test actual MySQL writes and regression of products/categories locally before merge.
