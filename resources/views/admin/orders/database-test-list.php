<?php
declare(strict_types=1);
/** @var array $orders */
/** @var int $total */
/** @var int $page */
/** @var int $pages */
/** @var string $search */
/** @var string $status */
use App\Services\Admin\OrderAdminService as Rules;
$pageTitle='Quản lý đơn hàng';$e=static fn($v)=>htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');$money=static fn($v)=>number_format((float)$v,0,',','.').' đ';ob_start(); ?>
<p class="rounded bg-amber-50 p-3 text-amber-900 mb-4">Chỉ đơn mẫu local. Không trừ tồn, không thu tiền, không gọi vận chuyển.</p>
<form class="flex flex-wrap gap-2 mb-4" method="get" action="/admin/orders"><input name="q" value="<?= $e($search) ?>" placeholder="Mã đơn hoặc email" class="border p-2 rounded"><select name="status" class="border rounded p-2"><option value="">Tất cả trạng thái</option><?php foreach(Rules::LABELS as $code=>$label): ?><option value="<?= $e($code) ?>" <?= $status===$code?'selected':'' ?>><?= $e($label) ?></option><?php endforeach; ?></select><button class="bg-blue-600 text-white px-4 rounded">Tìm kiếm</button><a class="p-2" href="/admin/orders">Bỏ lọc</a></form>
<p class="mb-2">Tổng: <?= $total ?> đơn mẫu</p><div class="overflow-x-auto rounded bg-white border"><table class="w-full text-left text-sm"><thead><tr><?php foreach(['Mã đơn','Email','Trạng thái','Tổng dự kiến','Ngày tạo','Thao tác'] as $label): ?><th class="p-3"><?= $e($label) ?></th><?php endforeach; ?></tr></thead><tbody><?php foreach($orders as $o): ?><tr class="border-t"><td class="p-3"><?= $e($o['checkout_key']) ?></td><td class="p-3"><?= $e($o['email']??'Không có') ?></td><td class="p-3"><?= $e(Rules::LABELS[$o['status']]??$o['status']) ?></td><td class="p-3"><?= $money($o['items_total']+$o['tax_total']+$o['shipping_fee']) ?></td><td class="p-3"><?= $e($o['created_at']) ?></td><td class="p-3"><a class="text-blue-700 underline" href="/admin/orders/<?= (int)$o['id'] ?>">Chi tiết</a></td></tr><?php endforeach; ?><?php if(!$orders): ?><tr><td colspan="6" class="p-4">Không có đơn mẫu phù hợp.</td></tr><?php endif; ?></tbody></table></div>
<div class="flex gap-4 mt-4"><?php if($page>1): ?><a href="?<?= $e(http_build_query(['q'=>$search,'status'=>$status,'page'=>$page-1])) ?>">Trang trước</a><?php endif; ?><span>Trang <?= $page ?>/<?= $pages ?></span><?php if($page<$pages): ?><a href="?<?= $e(http_build_query(['q'=>$search,'status'=>$status,'page'=>$page+1])) ?>">Trang sau</a><?php endif; ?></div>
<?php $content=ob_get_clean();require dirname(__DIR__).'/layout.php'; ?>
