<?php
declare(strict_types=1);

/** @var int $brandId */
/** @var array $old */
/** @var string $error */
/** @var string $brandToken */
$pageTitle = $brandId ? 'Chỉnh sửa thương hiệu' : 'Thêm thương hiệu';
$e = static fn($value): string => htmlspecialchars(is_scalar($value) ? (string)$value : '', ENT_QUOTES, 'UTF-8');
ob_start();
?>
<section class="max-w-2xl">
    <h1 class="mb-6 text-2xl font-bold"><?= $e($pageTitle) ?></h1>
    <?php if (!empty($error)): ?><p class="mb-4 rounded-lg bg-red-50 p-3 text-red-700"><?= $e($error) ?></p><?php endif; ?>
    <form method="post" action="<?= $brandId ? '/admin/brands/'.(int)$brandId.'/update' : '/admin/brands' ?>" class="space-y-5 rounded-xl border bg-white p-6 shadow-sm">
        <input type="hidden" name="brand_token" value="<?= $e($brandToken) ?>">
        <div><label for="name" class="mb-2 block font-medium">Tên thương hiệu</label><input id="name" name="name" required maxlength="100" value="<?= $e($old['name'] ?? '') ?>" class="w-full rounded-lg border p-3" placeholder="Ví dụ: Sony"></div>
        <div><label for="slug" class="mb-2 block font-medium">Slug</label><input id="slug" name="slug" required maxlength="120" pattern="[a-z0-9]+(-[a-z0-9]+)*" value="<?= $e($old['slug'] ?? '') ?>" class="w-full rounded-lg border p-3" placeholder="sony"><p class="mt-1 text-sm text-slate-500">Chữ thường không dấu, số và dấu gạch ngang.</p></div>
        <div class="flex gap-3"><button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-white">Lưu thương hiệu</button><a href="/admin/brands" class="rounded-lg border px-4 py-2">Hủy</a></div>
    </form>
</section>
<?php $content = ob_get_clean(); require dirname(__DIR__) . '/layout.php'; ?>
