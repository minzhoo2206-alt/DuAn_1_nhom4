<?php
$e = static fn(mixed $v): string => htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
ob_start(); ?>
<div class="heading"><div><h1>Quản lý đơn hàng</h1><p>Danh sách đơn hàng. Việc chuyển trạng thái cần tích hợp Service của Nhuận.</p></div></div>
<section class="card"><form method="get" action="/admin/orders" class="toolbar"><input name="status" class="search" aria-label="Trạng thái" placeholder="Lọc trạng thái" value="<?= $e($status) ?>"><button class="btn" type="submit">Lọc</button><a class="btn" href="/admin/orders">Tất cả</a><span class="muted" style="margin-left:auto;font-size:12px">Tổng: <?= (int)$total ?></span></form>
<div class="tablewrap"><table><thead><tr><th>Mã đơn</th><th>Trạng thái</th><th>Ngày tạo</th><th>Chi tiết</th></tr></thead><tbody>
<?php foreach($items as $order): ?><tr><td><strong>#<?= (int)$order['id'] ?></strong></td><td><span class="status"><?= $e($order['status']) ?></span></td><td class="muted"><?= $e($order['created_at']) ?></td><td><a class="link" href="/admin/orders/<?= (int)$order['id'] ?>">Xem ›</a></td></tr><?php endforeach; ?>
<?php if(!$items): ?><tr><td colspan="4"><?php $emptyTitle = 'Không có đơn hàng.'; $emptyHint = 'Thử trạng thái khác hoặc quay lại sau.'; require dirname(__DIR__).'/components/empty.php'; ?></td></tr><?php endif; ?>
</tbody></table></div><div class="pagination">Trang <?= (int)$page ?>/<?= (int)$pages ?><div class="flex">
<?php if($page>1): ?><a class="btn small" href="?page=<?= $page-1 ?>&amp;status=<?= rawurlencode($status) ?>">‹ Trước</a><?php endif; ?>
<?php if($page<$pages): ?><a class="btn small" href="?page=<?= $page+1 ?>&amp;status=<?= rawurlencode($status) ?>">Tiếp ›</a><?php endif; ?></div></div></section>
<?php $content=ob_get_clean(); require dirname(__DIR__).'/layout.php'; ?>
