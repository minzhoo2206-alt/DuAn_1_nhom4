<?php
$pageTitle = $pageTitle ?? 'Admin';
$isPreview = $isPreview ?? false;
$rbacNav = $isPreview ? ($_SESSION['task17_nav'] ?? null) : null;

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> | FOURTECH</title>
    <?php if ($isPreview): ?>
        <script src="https://cdn.tailwindcss.com"></script>
    <?php endif; ?>
</head>
<body class="bg-slate-100 text-slate-900">
    <div class="min-h-screen md:flex">
        <aside class="bg-slate-900 p-5 text-white md:w-64 md:shrink-0">
            <h1 class="text-2xl font-black text-blue-400">FOURTECH</h1>
            <p class="mb-8 text-sm text-slate-400">ADMIN PANEL</p>
            <nav class="space-y-2">
                <?php foreach ([['/admin','Tổng quan',['Tổng quan']], ['/admin/products','Sản phẩm',['Quản lý sản phẩm','Thêm sản phẩm','Chỉnh sửa sản phẩm']], ['/admin/categories','Danh mục',['Quản lý danh mục','Thêm danh mục','Chỉnh sửa danh mục']]] as [$url,$label,$titles]): ?>
                    <a href="<?= $url ?>" class="block rounded-lg p-3 <?= in_array($pageTitle, $titles, true) ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800' ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></a>
                <?php endforeach; ?>
                <?php if (!empty($brandModuleEnabled)): ?>
                    <a href="/admin/brands" class="block rounded-lg p-3 <?= in_array($pageTitle, ['Quản lý thương hiệu','Thêm thương hiệu','Chỉnh sửa thương hiệu'], true) ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800' ?>">Thương hiệu</a>
                <?php else: ?>
                    <span class="block rounded-lg p-3 text-slate-400" aria-disabled="true">Thương hiệu</span>
                <?php endif; ?>
                <?php if (!empty($userModuleEnabled) && ($rbacNav === null || !empty($rbacNav['task17.users.manage']))): ?>
                    <a href="/admin/users" class="block rounded-lg p-3 <?= in_array($pageTitle, ['Quản lý tài khoản','Thêm nhân viên','Chỉnh sửa tài khoản','Đặt lại mật khẩu'], true) ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800' ?>">Tài khoản</a>
                <?php else: ?>
                    <span class="block rounded-lg p-3 text-slate-400" aria-disabled="true">Tài khoản</span>
                <?php endif; ?>
                <?php if (!empty($rbacNav['task17.rbac.manage'])): ?><a href="/admin/rbac" class="block rounded-lg p-3 <?= $pageTitle==='Phân quyền RBAC'?'bg-blue-600 text-white':'text-slate-300 hover:bg-slate-800' ?>">Phân quyền</a><?php endif; ?>
                <?php if (!empty($rbacNav['task18.orders.manage'])): ?>
                    <a href="/admin/orders" class="block rounded-lg p-3 <?= in_array($pageTitle,['Quản lý đơn hàng','Chi tiết đơn hàng'],true)?'bg-blue-600 text-white':'text-slate-300 hover:bg-slate-800' ?>">Đơn hàng</a>
                <?php else: ?><span class="block rounded-lg p-3 text-slate-400" aria-disabled="true">Đơn hàng</span><?php endif; ?>
                <?php foreach (!empty($rbacNav['task17.rbac.manage']) ? [] : ['Phân quyền'] as $menu): ?>
                    <span class="block rounded-lg p-3 text-slate-400" aria-disabled="true"><?= htmlspecialchars($menu, ENT_QUOTES, 'UTF-8') ?></span>
                <?php endforeach; ?>
            </nav>
        </aside>
        <div class="min-w-0 flex-1">
            <header class="flex items-center justify-between border-b bg-white p-5">
                <h2 class="text-xl font-semibold"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h2>
                <?php if ($rbacNav !== null): ?>
                <form method="post" action="/admin/logout"><input type="hidden" name="rbac_token" value="<?= htmlspecialchars((string)($_SESSION['task17_csrf']??''), ENT_QUOTES, 'UTF-8') ?>"><button class="rounded border px-3 py-2 text-sm">Đăng xuất</button></form>
                <?php else: ?><span class="rounded-full bg-slate-100 px-3 py-2 text-sm">Admin</span><?php endif; ?>
            </header>
            <main class="p-4 md:p-8">
                <?= $content ?? '' ?>
            </main>
        </div>
    </div>
</body>
</html>
