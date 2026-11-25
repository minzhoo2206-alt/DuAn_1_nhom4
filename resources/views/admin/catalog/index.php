<?php
$e = static fn(mixed $v): string => htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$canCreate = in_array($kind.'.create', $navPermissions ?? [], true);
$canEdit = in_array($kind.'.update', $navPermissions ?? [], true);
ob_start();
?>
<div class="heading"><div><h1>Quản lý <?= $e($label) ?></h1><p>Tìm kiếm, chỉnh sửa và quản lý hiển thị dữ liệu.</p></div>
<?php if ($canCreate): ?><a class="btn primary" href="/admin/<?= $e($kind) ?>/create">+ Thêm mới</a><?php endif; ?></div>
<section class="card"><form class="toolbar" method="get" action="/admin/<?= $e($kind) ?>">
<input aria-label="Tìm tên hoặc slug" name="q" value="<?= $e($search) ?>" class="search" placeholder="Tìm tên, đường dẫn...">
<button class="btn" type="submit">Tìm kiếm</button><a class="btn" href="/admin/<?= $e($kind) ?>">Đặt lại</a><span class="muted" style="font-size:12px;margin-left:auto">Tổng: <strong><?= (int) $total ?></strong></span></form>
<div class="tablewrap"><table><thead><tr><th>ID</th><th>Tên</th><th>Slug</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody>
<?php foreach ($items as $item): ?><tr>
<td class="id">#<?= (int) $item['id'] ?></td><td><strong><?= $e($item['name']) ?></strong></td><td class="muted"><?= $e($item['slug']) ?></td>
<td><span class="status <?= $e($item['status']) ?>"><?= $e($item['status']) ?></span></td><td><div class="actions">
<?php if ($canEdit): ?><a class="btn small" href="/admin/<?= $e($kind) ?>/<?= (int) $item['id'] ?>/edit">Sửa</a>
<form method="post" action="/admin/<?= $e($kind) ?>/<?= (int) $item['id'] ?>/toggle" onsubmit="return confirm('Xác nhận thay đổi trạng thái?')">
<input type="hidden" name="_csrf" value="<?= $e($csrfToken) ?>"><button class="btn small warn" type="submit"><?= $item['status'] === 'active' ? 'Ẩn' : 'Hiện' ?></button></form>
<?php else: ?><span class="muted">Chỉ xem</span><?php endif; ?></div></td></tr><?php endforeach; ?>
<?php if (!$items): ?><tr><td colspan="5"><?php $emptyTitle = 'Không có '.$label.' phù hợp.'; $emptyHint = 'Thử từ khóa khác hoặc thêm dữ liệu mới.'; require dirname(__DIR__).'/components/empty.php'; ?></td></tr><?php endif; ?>
</tbody></table></div><div class="pagination"><span>Trang <?= (int)$page ?>/<?= (int)$pages ?></span><div class="flex">
<?php if ($page > 1): ?><a class="btn small" href="?page=<?= $page-1 ?>&amp;q=<?= rawurlencode($search) ?>">‹ Trước</a><?php endif; ?>
<?php if ($page < $pages): ?><a class="btn small" href="?page=<?= $page+1 ?>&amp;q=<?= rawurlencode($search) ?>">Tiếp ›</a><?php endif; ?>
</div></div></section>
<?php $content=ob_get_clean(); require dirname(__DIR__).'/layout.php'; ?>
