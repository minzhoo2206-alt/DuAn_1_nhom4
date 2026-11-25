<?php ob_start(); ?>
<div class="heading"><div><h1>Bảng điều khiển</h1><p>Chào mừng đến khu vực quản lý FOURTECH.</p></div><span class="status active">Hệ thống quản trị</span></div>
<div class="stats">
<?php foreach (['products'=>'Sản phẩm','categories'=>'Danh mục','brands'=>'Thương hiệu'] as $kind=>$label): ?>
<div class="card stat"><span class="muted"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span><div class="value"><?= number_format((int) ($counts[$kind] ?? 0)) ?></div><a href="/admin/<?= $kind ?>" class="link">Xem danh sách ›</a></div>
<?php endforeach; ?></div>
<div class="card card-pad"><h2 style="margin:0 0 7px;font-size:18px">Công việc quản trị</h2><p class="muted" style="font-size:13px;line-height:1.7">Quản lý các sản phẩm, danh mục, thương hiệu, kiểm tra đơn hàng và phân quyền truy cập. Các thao tác đều được kiểm tra quyền ở backend.</p></div>
<?php $content=ob_get_clean(); require __DIR__.'/layout.php'; ?>
