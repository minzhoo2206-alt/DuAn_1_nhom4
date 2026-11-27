<?php

declare(strict_types=1);

/** @var array $products */
/** @var int $total */
/** @var int $totalPages */
/** @var int $page */
/** @var string $success */
/** @var bool $isPreview */
$pageTitle = 'Quản lý sản phẩm';
$e = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$statusLabels = ['draft' => 'Bản nháp', 'active' => 'Đang bán', 'hidden' => 'Đang ẩn'];
$statusColors = ['draft' => 'bg-yellow-100 text-yellow-700', 'active' => 'bg-green-100 text-green-700', 'hidden' => 'bg-slate-100 text-slate-600'];
ob_start();
?>
<section>
    <h1 class="mb-1 text-2xl font-bold">Quản lý sản phẩm</h1>
    <p class="mb-4 text-slate-500">Tổng cộng <?= (int) $total ?> sản phẩm chưa bị xóa mềm</p>
    <p class="mb-4 rounded-lg bg-blue-50 p-3 text-sm text-blue-700">MySQL local · fourtech_admin_test. Hỗ trợ danh sách, thêm và sửa sản phẩm. Không sử dụng hosting.</p>
    <?php if (!empty($success)): ?><p role="status" class="mb-4 rounded-lg bg-green-100 p-3 text-green-800"><?= $e($success) ?></p><?php endif; ?>
    <a href="/admin/products/create" class="mb-4 inline-block rounded-lg bg-blue-600 px-4 py-2 text-white">+ Thêm sản phẩm</a>
    <div class="overflow-x-auto rounded-xl border bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-100"><tr>
                <?php foreach (['ID', 'Tên sản phẩm', 'Thương hiệu', 'Danh mục', 'Trạng thái', 'Thao tác'] as $heading): ?>
                    <th class="p-4"><?= $e($heading) ?></th>
                <?php endforeach; ?>
            </tr></thead>
            <tbody>
                <?php foreach ($products as $product): $status = $product['status'] ?? ''; ?>
                    <tr class="border-t hover:bg-slate-50">
                        <td class="p-4"><?= (int) $product['id'] ?></td>
                        <td class="p-4 font-semibold"><?= $e($product['name']) ?></td>
                        <td class="p-4"><?= $e($product['brand_name'] ?? '') ?></td>
                        <td class="p-4"><?= $e($product['category_name'] ?? '') ?></td>
                        <td class="p-4"><span class="rounded-full px-3 py-1 <?= $statusColors[$status] ?? 'bg-slate-100' ?>"><?= $e($statusLabels[$status] ?? 'Không xác định') ?></span></td>
                        <td class="p-4"><a class="inline-block rounded-lg border border-blue-600 px-3 py-2 font-medium text-blue-700 hover:bg-blue-50" href="/admin/products/<?= (int) $product['id'] ?>/edit">Sửa</a></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$products): ?><tr><td colspan="6" class="p-10 text-center text-slate-500">Chưa có sản phẩm.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <nav aria-label="Phân trang" class="mt-6 flex flex-wrap justify-end gap-2">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="/admin/products?page=<?= $i ?>" <?= $i === $page ? 'aria-current="page"' : '' ?> class="rounded-lg border px-4 py-2 <?= $i === $page ? 'bg-blue-600 text-white' : 'bg-white text-slate-700' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </nav>
</section>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layout.php';
