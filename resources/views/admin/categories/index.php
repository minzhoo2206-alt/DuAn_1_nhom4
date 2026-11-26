<?php
$pageTitle = 'Quản lý danh mục';
$e = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
ob_start();
?>
<section>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div><h1 class="text-2xl font-bold">Quản lý danh mục</h1><p class="text-slate-500">Tổng cộng <?= count($categories) ?> danh mục</p></div>
        <a href="/admin/categories/create" class="rounded-lg bg-blue-600 px-4 py-2 text-white">+ Thêm danh mục</a>
    </div>
    <?php if (!empty($isPreview)): ?><p class="mb-4 rounded-lg bg-blue-50 p-3 text-blue-700">Dữ liệu demo lưu trong Session.</p><?php endif; ?>
    <?php if (!empty($success)): ?><p class="mb-4 rounded-lg bg-green-50 p-3 text-green-700"><?= $e($success) ?></p><?php endif; ?>
    <div class="overflow-x-auto rounded-xl border bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-100"><tr><th class="p-4">ID</th><th class="p-4">Tên danh mục</th><th class="p-4">Slug</th><th class="p-4">Trạng thái</th><th class="p-4">Thao tác</th></tr></thead>
            <tbody>
            <?php foreach ($categories as $category): $id = (int)$category['id']; $active = (bool)$category['is_active']; ?>
                <tr class="border-t">
                    <td class="p-4"><?= $id ?></td><td class="p-4 font-semibold"><?= $e($category['name']) ?></td><td class="p-4"><?= $e($category['slug']) ?></td>
                    <td class="p-4"><span class="rounded-full px-3 py-1 <?= $active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600' ?>"><?= $active ? 'Đang hoạt động' : 'Ngừng sử dụng' ?></span></td>
                    <td class="p-4"><div class="flex flex-wrap gap-3">
                        <a class="text-blue-600" href="/admin/categories/<?= $id ?>/edit">Sửa</a>
                        <?php foreach ($active ? ['deactivate'=>'Ngừng sử dụng'] : ['activate'=>'Kích hoạt','delete'=>'Xóa mềm'] as $action=>$label): ?>
                            <form method="post" action="/admin/categories/<?= $id ?>/<?= $action ?>" onsubmit="return confirm('Xác nhận thao tác với danh mục này?')">
                                <input type="hidden" name="category_token" value="<?= $e($categoryToken) ?>">
                                <button class="<?= $action === 'delete' ? 'text-red-600' : 'text-slate-700' ?>" type="submit"><?= $e($label) ?></button>
                            </form>
                        <?php endforeach; ?>
                    </div></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$categories): ?><tr><td colspan="5" class="p-8 text-center text-slate-500">Chưa có danh mục.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <p class="mt-4 text-sm text-slate-500">Danh mục còn sản phẩm không thể ngừng sử dụng hoặc xóa mềm.</p>
</section>
<?php $content = ob_get_clean(); require dirname(__DIR__) . '/layout.php'; ?>
