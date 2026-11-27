<?php
declare(strict_types=1);
/** @var array $dashboard */
$pageTitle = 'Tổng quan';
// Keep old demo usable outside the local MySQL router; never show fake totals.
$live = isset($dashboard);
$stats = $dashboard['stats'] ?? ['Sản phẩm'=>'—','Danh mục'=>'—','Thương hiệu'=>'—'];
$orders = $dashboard['orders'] ?? [];
$canOrders = $dashboard['canOrders'] ?? false;
$e = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
ob_start();
?>
<section class="space-y-6">
    <div class="rounded-2xl bg-slate-900 p-6 text-white">
        <p class="text-xs font-semibold uppercase tracking-widest text-blue-300">FOURTECH / ADMIN</p>
        <h2 class="mt-2 text-2xl font-bold">Tổng quan cửa hàng</h2>
        <p class="mt-2 text-sm text-slate-300"><?= $live ? 'Thống kê trực tiếp từ MySQL local. Chỉ dùng cho kiểm thử.' : 'Chưa kết nối thống kê MySQL.' ?></p>
    </div>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <?php foreach ($stats as $label=>$value): ?>
            <article class="rounded-xl border bg-white p-5 shadow-sm">
                <p class="text-sm text-slate-500"><?= $e($label) ?></p>
                <p class="mt-3 text-2xl font-bold text-slate-900 break-words"><?= $e($value) ?></p>
            </article>
        <?php endforeach; ?>
    </div>
    <?php if ($canOrders): ?>
    <p class="rounded-lg border border-blue-200 bg-blue-50 p-3 text-sm text-blue-800">COD đơn mẫu đã giao chỉ là tổng giá trị mô phỏng, chưa xác nhận thu tiền hoặc đối soát, không phải doanh thu thật.</p>
    <div class="rounded-xl border bg-white p-5 shadow-sm">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2"><h3 class="text-lg font-bold">Đơn hàng mẫu gần đây</h3><a href="/admin/orders" class="text-sm font-medium text-blue-600">Xem tất cả</a></div>
        <div class="overflow-x-auto"><table class="w-full text-left text-sm"><thead class="bg-slate-100"><tr><th class="p-3">Mã đơn</th><th class="p-3">Trạng thái</th><th class="p-3">COD</th><th class="p-3">Ngày tạo</th></tr></thead><tbody>
        <?php foreach ($orders as $order): ?>
        <tr class="border-b"><td class="p-3"><a class="text-blue-600" href="/admin/orders/<?= (int)$order['id'] ?>"><?= $e($order['checkout_key']) ?></a></td><td class="p-3"><?= $e(\App\Services\Admin\OrderAdminService::LABELS[$order['status']] ?? $order['status']) ?></td><td class="p-3"><?= $e(number_format((float)$order['cod_total'],0,',','.')) ?> ₫</td><td class="p-3"><?= $e($order['created_at']) ?></td></tr>
        <?php endforeach; ?>
        <?php if (!$orders): ?><tr><td colspan="4" class="p-8 text-center text-slate-500">Chưa có đơn hàng mẫu.</td></tr><?php endif; ?>
        </tbody></table></div>
    </div>
    <?php endif; ?>
</section>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
