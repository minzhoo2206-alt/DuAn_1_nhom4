<?php

declare(strict_types=1);

/** @var array $products */
/** @var int $total */
/** @var int $totalPages */
/** @var int $page */
/** @var string $success */
/** @var bool $isPreview */
/** @var string $actionSecret */
/** @var array $filters */
/** @var array $filterOptions */
/** @var bool $canWrite */
// Defaults preserve legacy preview compatibility.
$filters = $filters ?? ['q'=>'','brand_id'=>0,'category_id'=>0,'status'=>''];
$filterOptions = $filterOptions ?? ['brands'=>[], 'categories'=>[]];
$canWrite = $canWrite ?? false;
$pageTitle = 'Quản lý sản phẩm';
$e = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$statusLabels = ['draft' => 'Bản nháp', 'active' => 'Đang bán', 'hidden' => 'Đang ẩn'];
$statusColors = ['draft' => 'bg-yellow-100 text-yellow-700', 'active' => 'bg-green-100 text-green-700', 'hidden' => 'bg-slate-100 text-slate-600'];
ob_start();
?>
<section>
    <h1 class="mb-1 text-2xl font-bold">Quản lý sản phẩm</h1>
    <p class="mb-4 text-slate-500">Tìm thấy <?= (int) $total ?> sản phẩm (chưa bị xóa mềm)</p>
    <p class="mb-4 rounded-lg bg-blue-50 p-3 text-sm text-blue-700">MySQL local · fourtech_admin_test. Trạng thái active chỉ dùng kiểm thử catalog; chưa xác nhận giá, IMEI và tồn kho thực tế.</p>
    <?php if (!empty($success)): ?><p role="status" class="mb-4 rounded-lg bg-green-100 p-3 text-green-800"><?= $e($success) ?></p><?php endif; ?>
    <form method="get" action="/admin/products" class="mb-4 grid gap-3 rounded-xl border bg-white p-4 md:grid-cols-2 xl:grid-cols-4">
        <label class="text-sm font-medium">Tìm sản phẩm<input name="q" maxlength="200" value="<?= $e($filters['q']) ?>" placeholder="Tên hoặc slug" class="mt-1 w-full rounded-lg border p-2"></label>
        <?php foreach (['brand_id'=>['Thương hiệu','brands'],'category_id'=>['Danh mục','categories']] as $key => [$label,$list]): ?>
        <label class="text-sm font-medium"><?= $e($label) ?><select name="<?= $e($key) ?>" class="mt-1 w-full rounded-lg border p-2"><option value="0">Tất cả</option>
            <?php foreach ($filterOptions[$list] as $opt): ?><option value="<?= (int) $opt['id'] ?>" <?= (int)$opt['id'] === $filters[$key] ? 'selected' : '' ?>><?= $e($opt['name']) ?></option><?php endforeach; ?>
        </select></label>
        <?php endforeach; ?>
        <label class="text-sm font-medium">Trạng thái<select name="status" class="mt-1 w-full rounded-lg border p-2"><option value="">Tất cả</option>
            <?php foreach ($statusLabels as $key=>$label): ?><option value="<?= $e($key) ?>" <?= $key === $filters['status'] ? 'selected' : '' ?>><?= $e($label) ?></option><?php endforeach; ?>
        </select></label>
        <div class="flex gap-2 md:col-span-2 xl:col-span-4"><button class="rounded-lg bg-blue-600 px-4 py-2 text-white">Tìm / Lọc</button><a href="/admin/products" class="rounded-lg border px-4 py-2">Bỏ lọc</a></div>
    </form>
    <?php if ($canWrite): ?>
    <a href="/admin/products/create" class="mb-4 inline-block rounded-lg bg-blue-600 px-4 py-2 text-white">+ Thêm sản phẩm</a>
    <?php endif; ?>
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
                        <td class="p-4">
                            <?php if ($canWrite): ?>
                            <div class="flex flex-wrap items-center gap-2">
                                <a class="inline-block rounded-lg border border-blue-600 px-3 py-2 font-medium text-blue-700 hover:bg-blue-50" href="/admin/products/<?= (int) $product['id'] ?>/edit">Sửa</a>
                                <?php if ($status === 'active'): ?>
                                    <form method="post" action="/admin/products/<?= (int) $product['id'] ?>/hide" onsubmit="return confirm('Ẩn sản phẩm đang bán này?');">
                                        <?php foreach ($filters + ['page'=>$page] as $key=>$value): ?><input type="hidden" name="<?= $e($key) ?>" value="<?= $e($value) ?>"><?php endforeach; ?>
                                        <input type="hidden" name="admin_product_action_token" value="<?= $e(hash_hmac('sha256', 'hide:' . (int) $product['id'], $actionSecret)) ?>">
                                        <button type="submit" class="rounded-lg border border-amber-600 px-3 py-2 text-amber-700 hover:bg-amber-50">Ẩn</button>
                                    </form>
                                <?php elseif (in_array($status, ['draft', 'hidden'], true)): ?>
                                    <form method="post" action="/admin/products/<?= (int) $product['id'] ?>/activate" onsubmit="return confirm('Kích hoạt sản phẩm kiểm thử này?');">
                                        <?php foreach ($filters + ['page'=>$page] as $key=>$value): ?><input type="hidden" name="<?= $e($key) ?>" value="<?= $e($value) ?>"><?php endforeach; ?>
                                        <input type="hidden" name="admin_product_action_token" value="<?= $e(hash_hmac('sha256', 'activate:' . (int) $product['id'], $actionSecret)) ?>">
                                        <button type="submit" class="rounded-lg border border-green-600 px-3 py-2 text-green-700 hover:bg-green-50">Kích hoạt</button>
                                    </form>
                                    <form method="post" action="/admin/products/<?= (int) $product['id'] ?>/delete" onsubmit="return confirm('Xóa mềm sản phẩm này? Bản ghi vẫn được giữ trong MySQL.');">
                                        <?php foreach ($filters + ['page'=>$page] as $key=>$value): ?><input type="hidden" name="<?= $e($key) ?>" value="<?= $e($value) ?>"><?php endforeach; ?>
                                        <input type="hidden" name="admin_product_action_token" value="<?= $e(hash_hmac('sha256', 'delete:' . (int) $product['id'], $actionSecret)) ?>">
                                        <button type="submit" class="rounded-lg border border-red-600 px-3 py-2 text-red-700 hover:bg-red-50">Xóa mềm</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                            <?php else: ?><span class="text-slate-500">Chỉ xem</span><?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$products): ?><tr><td colspan="6" class="p-10 text-center text-slate-500">Không tìm thấy sản phẩm phù hợp.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <nav aria-label="Phân trang" class="mt-6 flex flex-wrap justify-end gap-2">
        <span class="self-center text-sm text-slate-500">Trang <?= (int)$page ?>/<?= (int)$totalPages ?></span>
        <?php if ($page > 1): ?><a class="rounded-lg border px-3 py-2" href="/admin/products?<?= $e(http_build_query($filters + ['page'=>1])) ?>">Đầu</a><?php endif; ?>
        <?php for ($i = max(1, $page - 3); $i <= min($totalPages, $page + 3); $i++): ?>
            <a href="/admin/products?<?= $e(http_build_query($filters + ['page'=>$i])) ?>" <?= $i === $page ? 'aria-current="page"' : '' ?> class="rounded-lg border px-4 py-2 <?= $i === $page ? 'bg-blue-600 text-white' : 'bg-white text-slate-700' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?><a class="rounded-lg border px-3 py-2" href="/admin/products?<?= $e(http_build_query($filters + ['page'=>$totalPages])) ?>">Cuối</a><?php endif; ?>
    </nav>
</section>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layout.php';
