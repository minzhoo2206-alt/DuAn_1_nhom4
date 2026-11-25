<?php

declare(strict_types=1);

$pageTitle = 'Quản lý sản phẩm';

$products = $products ?? [];
$page = $page ?? 1;
$total = $total ?? 0;
$totalPages = $totalPages ?? 1;

$statusLabels = [
    'draft' => 'Bản nháp',
    'active' => 'Đang bán',
    'hidden' => 'Đang ẩn',
];

$statusColors = [
    'draft' => 'bg-yellow-100 text-yellow-700',
    'active' => 'bg-green-100 text-green-700',
    'hidden' => 'bg-slate-100 text-slate-600',
];

ob_start();

?>

<section>

    <!-- Tiêu đề -->
    <div class="mb-6 flex items-center justify-between">

        <div>
            <h1 class="text-2xl font-bold">
                Quản lý sản phẩm
            </h1>

            <p class="mt-1 text-slate-500">
                Tổng cộng <?= (int) $total ?> sản phẩm
            </p>
        </div>

        <button disabled
            title="Triển khai ở Task 11"
            class="rounded-lg bg-blue-600 px-4 py-2 text-white opacity-60">

            + Thêm sản phẩm

        </button>

    </div>

    <?php if (!empty($isPreview)): ?>

        <p class="mb-4 rounded-lg bg-blue-50 p-3 text-sm text-blue-700">
            Đang hiển thị dữ liệu mẫu để chạy thử.
        </p>

    <?php endif; ?>

    <!-- Bảng sản phẩm -->
    <div class="overflow-x-auto rounded-xl border bg-white shadow-sm">

        <table class="w-full text-left text-sm">

            <thead class="bg-slate-100">

                <tr>
                    <th class="p-4">ID</th>
                    <th class="p-4">Tên sản phẩm</th>
                    <th class="p-4">Thương hiệu</th>
                    <th class="p-4">Danh mục</th>
                    <th class="p-4">Trạng thái</th>
                    <th class="p-4">Thao tác</th>
                </tr>

            </thead>

            <tbody>

                <?php foreach ($products as $product): ?>

                    <?php
                    $status = $product['status'] ?? 'draft';
                    ?>

                    <tr class="border-t hover:bg-slate-50">

                        <td class="p-4">
                            <?= (int) $product['id'] ?>
                        </td>

                        <td class="p-4 font-semibold">
                            <?= htmlspecialchars(
                                $product['name'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </td>

                        <td class="p-4">
                            <?= htmlspecialchars(
                                $product['brand_name'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </td>

                        <td class="p-4">
                            <?= htmlspecialchars(
                                $product['category_name'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </td>

                        <td class="p-4">

                            <span class="rounded-full px-3 py-1
                                <?= $statusColors[$status]
                                    ?? 'bg-slate-100' ?>">

                                <?= $statusLabels[$status]
                                    ?? 'Không xác định' ?>

                            </span>

                        </td>

                        <td class="p-4">

                            <button disabled
                                class="mr-2 text-blue-600 opacity-50">
                                Sửa
                            </button>

                            <button disabled
                                class="text-red-600 opacity-50">
                                Xóa
                            </button>

                        </td>

                    </tr>

                <?php endforeach; ?>

                <?php if (empty($products)): ?>

                    <tr>
                        <td colspan="6"
                            class="p-10 text-center text-slate-500">

                            Chưa có sản phẩm.

                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

    <!-- Phân trang -->
    <div class="mt-6 flex flex-wrap justify-end gap-2">

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>

            <a href="/admin/products?page=<?= $i ?>"
                class="rounded-lg border px-4 py-2
                <?= $i === $page
                    ? 'bg-blue-600 text-white'
                    : 'bg-white text-slate-700' ?>">

                <?= $i ?>

            </a>

        <?php endfor; ?>

    </div>

</section>

<?php

$content = ob_get_clean();

require dirname(__DIR__) . '/layout.php';
