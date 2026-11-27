<?php
declare(strict_types=1);

/** @var array $brands */
/** @var array $categories */



$pageTitle = 'Quản lý sản phẩm';
$old = $old ?? [];
$e = static fn($value): string => htmlspecialchars(is_scalar($value) ? (string) $value : '', ENT_QUOTES, 'UTF-8');
ob_start();
?>
<section class="mx-auto max-w-3xl">
    <h1 class="mb-2 text-2xl font-bold">Chỉnh sửa sản phẩm</h1>
    <p class="mb-5 text-slate-500">Cập nhật thông tin sản phẩm #<?= (int) $productId ?>. Trạng thái được giữ nguyên.</p>
    <?php if (!empty($isPreview)): ?>
        <p class="mb-4 rounded-lg bg-blue-50 p-3 text-blue-700">Demo local: dữ liệu lưu trong session, chưa kết nối MySQL.</p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <p role="alert" class="mb-4 rounded-lg bg-red-50 p-3 text-red-700"><?= $e($error) ?></p>
    <?php endif; ?>
    <form method="post" action="/admin/products/<?= (int) $productId ?>/update" class="space-y-4 rounded-xl border bg-white p-6">
        <input type="hidden" name="admin_product_token" value="<?= $e($adminProductToken ?? '') ?>">
        <!-- Token CSRF middleware chung sẽ được bổ sung theo contract của Minh. -->
        <label class="block">Tên sản phẩm
            <input required name="name" maxlength="200" value="<?= $e($old['name'] ?? '') ?>" class="mt-1 w-full rounded-lg border p-3">
        </label>
        <label class="block">Slug (tên đường dẫn)
            <input required name="slug" maxlength="220" placeholder="iphone-16-pro-max" value="<?= $e($old['slug'] ?? '') ?>" class="mt-1 w-full rounded-lg border p-3">
        </label>
        <div class="grid gap-4 sm:grid-cols-2">
            <label>Thương hiệu
                <select required name="brand_id" class="mt-1 w-full rounded-lg border p-3">
                    <option value="">-- Chọn thương hiệu --</option>
                    <?php foreach ($brands as $brand): ?>
                        <option value="<?= (int) $brand['id'] ?>" <?= (string) $brand['id'] === $e($old['brand_id'] ?? '') ? 'selected' : '' ?>><?= $e($brand['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Danh mục
                <select required name="category_id" class="mt-1 w-full rounded-lg border p-3">
                    <option value="">-- Chọn danh mục --</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category['id'] ?>" <?= (string) $category['id'] === $e($old['category_id'] ?? '') ? 'selected' : '' ?>><?= $e($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>
        <label class="block">Thời hạn bảo hành (tháng)
            <input required type="number" min="0" max="65535" name="warranty_months" value="<?= $e($old['warranty_months'] ?? '12') ?>" class="mt-1 w-full rounded-lg border p-3">
        </label>
        <label class="block">Mô tả
            <textarea name="description" rows="4" class="mt-1 w-full rounded-lg border p-3"><?= $e($old['description'] ?? '') ?></textarea>
        </label>
        <div class="flex flex-wrap gap-3">
            <button type="submit" class="rounded-lg bg-blue-600 px-5 py-3 font-semibold text-white">Lưu thay đổi</button>
            <a href="/admin/products" class="rounded-lg border px-5 py-3">Hủy</a>
        </div>
    </form>
</section>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layout.php';
