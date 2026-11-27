<?php
declare(strict_types=1);

/** @var array $brands */
/** @var string $brandToken */
/** @var string $success */
/** @var string $notice */
$pageTitle = 'Quản lý thương hiệu';
$e = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
ob_start();
?>
<section>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div><h1 class="text-2xl font-bold">Quản lý thương hiệu</h1><p class="text-slate-500">Tổng cộng <?= count($brands) ?> thương hiệu</p></div>
        <a href="/admin/brands/create" class="rounded-lg bg-blue-600 px-4 py-2 text-white">+ Thêm thương hiệu</a>
    </div>
    <?php if (!empty($isPreview)): ?><p class="mb-4 rounded-lg bg-blue-50 p-3 text-blue-700">MySQL local · fourtech_admin_test. Không sử dụng hosting.</p><?php endif; ?>
    <?php if (!empty($success)): ?><p class="mb-4 rounded-lg bg-green-50 p-3 text-green-700"><?= $e($success) ?></p><?php endif; ?>
    <?php if (!empty($notice)): ?><p role="alert" class="mb-4 rounded-lg bg-red-50 p-3 text-red-700"><?= $e($notice) ?></p><?php endif; ?>
    <div class="overflow-x-auto rounded-xl border bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-100"><tr><th class="p-4">ID</th><th class="p-4">Tên thương hiệu</th><th class="p-4">Slug</th><th class="p-4">Trạng thái</th><th class="p-4">Thao tác</th></tr></thead>
            <tbody>
            <?php foreach ($brands as $brand): $id = (int)$brand['id']; $active = (bool)$brand['is_active']; ?>
                <tr class="border-t">
                    <td class="p-4"><?= $id ?></td><td class="p-4 font-semibold"><?= $e($brand['name']) ?></td><td class="p-4"><?= $e($brand['slug']) ?></td>
                    <td class="p-4"><span class="rounded-full px-3 py-1 <?= $active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600' ?>"><?= $active ? 'Đang hoạt động' : 'Ngừng sử dụng' ?></span></td>
                    <td class="p-4"><div class="flex flex-wrap gap-3">
                        <a class="text-blue-600" href="/admin/brands/<?= $id ?>/edit">Sửa</a>
                        <?php foreach ($active ? ['deactivate'=>'Ngừng sử dụng'] : ['activate'=>'Kích hoạt','delete'=>'Xóa mềm'] as $action=>$label): ?>
                            <form method="post" action="/admin/brands/<?= $id ?>/<?= $action ?>" onsubmit="return confirm('Xác nhận thao tác với thương hiệu này?')">
                                <input type="hidden" name="brand_token" value="<?= $e($brandToken) ?>">
                                <button class="<?= $action === 'delete' ? 'text-red-600' : 'text-slate-700' ?>" type="submit"><?= $e($label) ?></button>
                            </form>
                        <?php endforeach; ?>
                    </div></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$brands): ?><tr><td colspan="5" class="p-8 text-center text-slate-500">Chưa có thương hiệu.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <p class="mt-4 text-sm text-slate-500">Thương hiệu còn sản phẩm không thể ngừng sử dụng hoặc xóa mềm.</p>
</section>
<?php $content = ob_get_clean(); require dirname(__DIR__) . '/layout.php'; ?>
