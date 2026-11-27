# FOURTECH Task 19 — local demo seeder

Prerequisites: Task 14.3–18 schemas imported, Composer dependencies installed, `.env.admin-test` configured to `127.0.0.1` / `fourtech_admin_test`. Never import or seed hosting. Existing Task 17 admin remains untouched. No route changes or schema changes.

1. Stop local PHP server during seeding; avoid concurrent writes. Back up local database if needed.
2. `php docs/seed-admin-demo.php --check` validates fixture data and legal order state paths without DB.
3. `php docs/seed-admin-demo.php --apply` inserts missing rows in one transaction. Existing slugs/emails/order keys are not updated; soft-deleted seeded rows stay deleted. Existing inactive or deleted required brand/category stops the transaction. No passwords printed; customer fixtures get random unknown passwords and customer role only. Use the existing admin to inspect them.
4. Run `--apply` again: inserted counts should all be 0 if first run succeeded and no rows were removed. `skipped` means preserved inactive/deleted/conflicting fixture data, not an error. Existing catalog rows are reused by slug; totals can exceed fixture counts. Do not run concurrently.
5. Run `docs/admin-seed-verification.sql` in phpMyAdmin SQL tab, or copy individual queries. Start the existing local admin server.

Demo phone labels are illustrative, not current retail catalog data. Prices apply to synthetic order snapshots only; no product configuration/variant, price, image, serial/IMEI, stock, payment, or real fulfillment records are generated. Existing order status changes are preserved. The order paths use the local Task 18 service. Production seeding and full schema integration require the team. CLI uses direct PDO (isolated config) and does not edit Core. Offline validation does not prove MySQL execution. No DDL or delete commands are needed.
