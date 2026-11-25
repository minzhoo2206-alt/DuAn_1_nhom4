# Đề xuất route / contract cho Minh tích hợp (không tự sửa routes/web.php)

Đăng chỉ cung cấp Controller; **Minh sở hữu đăng ký route**, Core middleware/CSRF. Dùng `App\Controllers\\Admin\\...` theo PSR-4.

```php
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\CatalogController;
use App\Controllers\Admin\RoleController;
use App\Controllers\Admin\OrderAdminController;

// Minh thêm vào routes/web.php, bảo vệ auth + permission tại Router/Middleware.
$routes->addRoute('GET', '/admin', [DashboardController::class, 'index']);
$routes->addRoute('GET', '/admin/{kind:products|categories|brands}', [CatalogController::class, 'index']);
$routes->addRoute('GET', '/admin/{kind:products|categories|brands}/create', [CatalogController::class, 'create']);
$routes->addRoute('POST', '/admin/{kind:products|categories|brands}', [CatalogController::class, 'store']);
$routes->addRoute('GET', '/admin/{kind:products|categories|brands}/{id:\d+}/edit', [CatalogController::class, 'edit']);
$routes->addRoute('POST', '/admin/{kind:products|categories|brands}/{id:\d+}/edit', [CatalogController::class, 'update']);
$routes->addRoute('POST', '/admin/{kind:products|categories|brands}/{id:\d+}/toggle', [CatalogController::class, 'toggle']);
$routes->addRoute('GET', '/admin/roles', [RoleController::class, 'index']);
$routes->addRoute('POST', '/admin/roles/assign', [RoleController::class, 'assign']);
$routes->addRoute('POST', '/admin/roles/grant', [RoleController::class, 'grant']);
$routes->addRoute('GET', '/admin/orders', [OrderAdminController::class, 'index']);
$routes->addRoute('GET', '/admin/orders/{id:\d+}', [OrderAdminController::class, 'show']);
// Chưa bật POST /admin/orders/{id}/status: OrderService contract chưa có.
```

**Lưu ý:** Nếu Router của Minh đã có GET `/admin/products`, phải thay bằng 1 khai báo duy nhất, tránh route trùng. `FastRoute` phải truyền `array $vars` theo `Router.php` trong tài liệu.

## Bảng permission

| Route / tác vụ | Quyền tối thiểu |
| --- | --- |
| GET `/admin` | `dashboard.view` |
| GET `/{kind}` | `{kind}.view` |
| GET create, POST create | `{kind}.create` |
| GET edit, POST edit/toggle | `{kind}.update` |
| GET `/admin/roles` | `users.view` |
| POST `/admin/roles/assign` | `users.assign_roles` |
| POST `/admin/roles/grant` | `roles.assign_permissions` |
| GET `/admin/orders`, GET detail | `orders.view` |
| POST status (chưa bật) | `orders.update` |

Các route thay đổi dữ liệu dùng **POST `_csrf`**, `_csrf` được Admin tạo trong session. Minh cần chặn tại middleware (ngoài bảo vệ trong Controller) và thống nhất tên token. Session ID đã xác thực cần được Nhuận/Minh gán vào `$_SESSION['user_id']`; không nhận user ID từ request.

## Request / View DTO

- Controller: `App\Core\\Request::input($key)` hoặc `all()`, `Response::html()`, `redirect()` và `View::render()`.
- Catalog index: `items` (mảng id/name/slug/status), `total`, `page`, `pages`, `search`, `kind`, `label`, `csrfToken`, `navPermissions`.
- Catalog form: `item`, `errors[field=>message]`, `kind`, `label`, `csrfToken`.
- Role index: `users` id/email/role_ids, `roles` id/name.
- Order listing: `items` id/status/created_at, `page`, `pages`, `total`, `status`.
- Error responses: 401 guest, 403 missing permission, 404 missing record, 419 CSRF invalid, 422 invalid form, 500 DB/system error.

## Contract Nhuận cần bổ sung cho Admin Orders

Sau khi chốt state machine và DDL: cung cấp API service dùng chung để (1) getOrderDetail(id), (2) allowedTransitions(orderId,actorId), (3) transition(orderId,newStatus,actorId,idempotencyKey), (4) ghi history trong transaction, (5) không phát sinh tồn âm/oversell. Đăng chỉ gọi API này; tuyệt đối không `UPDATE orders` trực tiếp.
