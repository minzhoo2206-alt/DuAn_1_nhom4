<?php
$pageTitle = $pageTitle ?? 'Admin';
$isPreview = $isPreview ?? false;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>
        | FOURTECH
    </title>

    <?php if ($isPreview): ?>
        <script src="https://cdn.tailwindcss.com"></script>
    <?php endif; ?>
</head>

<body class="bg-slate-100 text-slate-900">

    <div class="min-h-screen md:flex">

        <!-- Sidebar -->
        <aside class="bg-slate-900 p-5 text-white md:w-64 md:shrink-0">

            <h1 class="text-2xl font-black text-blue-400">
                FOURTECH
            </h1>

            <p class="mb-8 text-sm text-slate-400">
                ADMIN PANEL
            </p>

            <nav class="space-y-2">

                <a href="/admin"
                   class="block rounded-lg bg-blue-600 p-3">
                    Tổng quan
                </a>

                <?php foreach ([
                    'Sản phẩm',
                    'Danh mục',
                    'Thương hiệu',
                    'Đơn hàng',
                    'Tài khoản',
                    'Phân quyền'
                ] as $menu): ?>

                    <span class="block rounded-lg p-3 text-slate-400"
                          aria-disabled="true">

                        <?= htmlspecialchars($menu, ENT_QUOTES, 'UTF-8') ?>

                    </span>

                <?php endforeach; ?>

            </nav>

        </aside>

        <!-- Nội dung bên phải -->
        <div class="min-w-0 flex-1">

            <!-- Header -->
            <header class="flex items-center justify-between border-b bg-white p-5">

                <h2 class="text-xl font-semibold">
                    <?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>
                </h2>

                <span class="rounded-full bg-slate-100 px-3 py-2 text-sm">
                    Admin
                </span>

            </header>

            <!-- Nội dung chính -->
            <main class="p-4 md:p-8">

                <?= $content ?? '' ?>

            </main>

        </div>

    </div>

</body>
</html>
