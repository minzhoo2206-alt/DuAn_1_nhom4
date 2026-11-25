<?php
$e = static fn(mixed $v): string => htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$isEdit = isset($item['id']) && (int) $item['id'] > 0;
$action = '/admin/'.$kind.($isEdit ? '/'.(int)$item['id'].'/edit' : '');
ob_start(); ?>
<div class="crumb"><a href="/admin/<?= $e($kind) ?>">Quản lý <?= $e($label) ?></a> / <?= $isEdit ? 'Chỉnh sửa' : 'Thêm mới' ?></div>
<div class="heading"><div><h1><?= $isEdit ? 'Chỉnh sửa ' : 'Thêm ' ?><?= $e($label) ?></h1><p>Thông tin sẽ được kiểm tra phía máy chủ trước khi lưu.</p></div></div>
<section class="card card-pad form-card">
<?php if ($errors): ?><div class="alert bad" role="alert">Vui lòng kiểm tra các trường được đánh dấu.</div><?php endif; ?>
<form method="post" action="<?= $e($action) ?>">
<input type="hidden" name="_csrf" value="<?= $e($csrfToken) ?>">
<div class="field"><label for="name">Tên <?= $e($label) ?> <span style="color:#dc3838">*</span></label>
<input id="name" class="input" name="name" maxlength="255" required value="<?= $e($item['name'] ?? '') ?>" placeholder="Nhập tên">
<?php if (isset($errors['name'])): ?><div class="error" role="alert"><?= $e($errors['name']) ?></div><?php endif; ?></div>
<div class="field"><label for="slug">Slug (đường dẫn) <span style="color:#dc3838">*</span></label>
<input id="slug" class="input" name="slug" maxlength="255" pattern="[a-z0-9]+(-[a-z0-9]+)*" required value="<?= $e($item['slug'] ?? '') ?>" placeholder="vi-du-ten-san-pham">
<div class="helper">Chỉ dùng a-z, 0-9 và dấu gạch ngang. Slug phải duy nhất.</div>
<?php if (isset($errors['slug'])): ?><div class="error" role="alert"><?= $e($errors['slug']) ?></div><?php endif; ?></div>
<div class="field"><label for="status">Trạng thái</label><select id="status" name="status" class="input">
<?php foreach (['draft'=>'Bản nháp','active'=>'Đang hiển thị','inactive'=>'Đang ẩn'] as $value=>$text): ?><option value="<?= $value ?>" <?= ($item['status'] ?? 'draft') === $value ? 'selected' : '' ?>><?= $text ?></option><?php endforeach; ?></select>
<?php if (isset($errors['status'])): ?><div class="error" role="alert"><?= $e($errors['status']) ?></div><?php endif; ?></div>
<div class="flex" style="padding-top:10px;border-top:1px solid #edf0f5"><button class="btn primary" type="submit"><?= $isEdit ? 'Lưu thay đổi' : 'Thêm mới' ?></button><a class="btn" href="/admin/<?= $e($kind) ?>">Hủy</a></div>
</form></section>
<?php $content=ob_get_clean(); require dirname(__DIR__).'/layout.php'; ?>
