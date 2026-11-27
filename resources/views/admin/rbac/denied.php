<?php
declare(strict_types=1);
/** @var array $actor */
/** @var string $token */
$e=static fn($v)=>htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
?>
<!doctype html><html lang="vi"><meta charset="utf-8"><title>403 | FOURTECH</title><main style="font:16px system-ui;max-width:600px;margin:60px auto;padding:24px"><h1>403 – Không đủ quyền</h1><p>Tài khoản <?= $e($actor['email']) ?> không được phép thực hiện thao tác này.</p><p><a href="/admin/products">Danh sách sản phẩm</a></p><form method="post" action="/admin/logout"><input type="hidden" name="rbac_token" value="<?= $e($token) ?>"><button>Đăng xuất</button></form></main></html>
