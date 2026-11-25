<?php
$e = static fn(mixed $v): string => htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
ob_start(); ?>
<div class="crumb"><a href="/admin/orders">Đơn hàng</a> / #<?= (int)$order['id'] ?></div>
<div class="heading"><div><h1>Chi tiết đơn #<?= (int)$order['id'] ?></h1><p>Thông tin trạng thái cơ bản từ cơ sở dữ liệu.</p></div></div>
<section class="card card-pad form-card"><p><strong>Mã đơn:</strong> #<?= (int)$order['id'] ?></p><p><strong>Trạng thái:</strong> <span class="status"><?= $e($order['status']) ?></span></p>
<p><strong>Ngày tạo:</strong> <?= $e($order['created_at']) ?></p><div class="alert bad">Chi tiết sản phẩm, thanh toán, vận chuyển và chuyển trạng thái cần OrderService/DTO do Nhuận cung cấp.</div>
<a class="btn" href="/admin/orders">← Quay lại</a></section>
<?php $content=ob_get_clean(); require dirname(__DIR__).'/layout.php'; ?>
