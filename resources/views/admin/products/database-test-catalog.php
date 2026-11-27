<?php
declare(strict_types=1);
/** @var array $product @var array $configurations @var array $variants @var array $colors @var array $images */
/** @var bool $canWrite @var bool $ready @var string $token @var string $error @var string $success */
$pageTitle='Chi tiết catalog';$e=static fn($v)=>htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');$url='/admin/products/'.(int)$product['id'].'/catalog';$field='rounded border p-2 w-full';$btn='rounded bg-blue-600 px-4 py-2 text-white';$date=static fn($v)=>$v?str_replace(' ','T',substr((string)$v,0,16)):'';$money=static fn($v)=>number_format((float)$v,0,'.',' ');
$hidden=static function(string $action,int $id)use($e,$token){?><input type="hidden" name="token" value="<?= $e($token) ?>"><input type="hidden" name="action" value="<?= $e($action) ?>"><input type="hidden" name="id" value="<?= $id ?>"><?php };ob_start();
?>
<a href="/admin/products" class="text-blue-700">← Danh sách sản phẩm</a>
<h1 class="my-4 text-2xl font-bold"><?= $e($product['name']) ?></h1>
<p class="mb-4 rounded bg-blue-50 p-3">Local test · <?= $ready?'Có cấu hình giá và biến thể hợp lệ.':'Chưa đủ cấu hình giá và biến thể để kích hoạt mới.' ?> Chưa xác nhận tồn kho, IMEI hay VAT thực tế.</p>
<?php if($error!==''):?><p role="alert" class="mb-4 rounded bg-red-100 p-3 text-red-800"><?= $e($error) ?></p><?php endif;?>
<?php if($success!==''):?><p role="status" class="mb-4 rounded bg-green-100 p-3"><?= $e($success) ?></p><?php endif;?>
<section class="mb-5 rounded-xl border bg-white p-4"><h2 class="mb-3 text-xl font-bold">1. Cấu hình và giá (VNĐ)</h2>
<?php foreach(array_merge($configurations,$canWrite?[['id'=>0,'storage_gb'=>256,'ram_gb'=>8,'list_price'=>'','sale_price'=>null,'sale_starts_at'=>null,'sale_ends_at'=>null,'is_active'=>1]]:[]) as $c):?>
<div class="mb-3 rounded border p-3"><h3 class="mb-2 font-semibold"><?= (int)$c['id']?'Cấu hình #'.(int)$c['id']:'Thêm cấu hình' ?></h3>
<?php if($canWrite):?><form method="post" action="<?= $e($url) ?>" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3"><?php $hidden('config',(int)$c['id']);?>
<label>Dung lượng (GB)<input class="<?= $field ?>" type="number" min="1" max="65535" name="storage_gb" required value="<?= $e($c['storage_gb']) ?>"></label>
<label>RAM (GB)<input class="<?= $field ?>" type="number" min="1" max="65535" name="ram_gb" required value="<?= $e($c['ram_gb']) ?>"></label>
<label>Giá niêm yết<input class="<?= $field ?>" type="number" min="1" max="999999999999" name="list_price" required value="<?= $e($c['list_price']===''?'':(string)(int)$c['list_price']) ?>"></label>
<label>Giá khuyến mãi<input class="<?= $field ?>" type="number" min="1" name="sale_price" value="<?= $e($c['sale_price']===null?'':(string)(int)$c['sale_price']) ?>"></label>
<label>Bắt đầu KM<input class="<?= $field ?>" type="datetime-local" name="sale_starts_at" value="<?= $e($date($c['sale_starts_at'])) ?>"></label>
<label>Kết thúc KM<input class="<?= $field ?>" type="datetime-local" name="sale_ends_at" value="<?= $e($date($c['sale_ends_at'])) ?>"></label>
<label>Trạng thái<select class="<?= $field ?>" name="is_active"><option value="1" <?= (int)$c['is_active']===1?'selected':'' ?>>Hoạt động</option><option value="0" <?= (int)$c['is_active']===0?'selected':'' ?>>Ngừng</option></select></label><div class="self-end"><button class="<?= $btn ?>">Lưu cấu hình</button></div></form>
<?php else:?><p><?= (int)$c['storage_gb'] ?>GB / <?= (int)$c['ram_gb'] ?>GB · <?= $money($c['list_price']) ?> đ<?= $c['sale_price']!==null?' · KM '.$money($c['sale_price']).' đ':'' ?> · <?= (int)$c['is_active']?'Hoạt động':'Ngừng' ?></p><?php endif;?></div><?php endforeach;?>
<?php if(!$configurations&&!$canWrite):?><p>Chưa có cấu hình.</p><?php endif;?></section>
<section class="mb-5 rounded-xl border bg-white p-4"><h2 class="mb-3 text-xl font-bold">2. Biến thể màu / SKU</h2>
<?php foreach(array_merge($variants,$canWrite&&$configurations?[['id'=>0,'configuration_id'=>0,'color_id'=>0,'sku'=>'','is_active'=>1]]:[]) as $v):?>
<div class="mb-3 rounded border p-3"><?php if($canWrite):?><form method="post" action="<?= $e($url) ?>" class="grid gap-3 sm:grid-cols-2"><?php $hidden('variant',(int)$v['id']);?>
<label>Cấu hình<select class="<?= $field ?>" name="configuration_id" required><option value="">Chọn cấu hình</option><?php foreach($configurations as $c):?><option value="<?= (int)$c['id'] ?>" <?= (int)$v['configuration_id']===(int)$c['id']?'selected':'' ?>><?= (int)$c['storage_gb'] ?>GB / <?= (int)$c['ram_gb'] ?>GB<?= (int)$c['is_active']?'':' (ngừng)' ?></option><?php endforeach;?></select></label>
<label>Màu<select class="<?= $field ?>" name="color_id" required><option value="">Chọn màu</option><?php foreach($colors as $c):?><option value="<?= (int)$c['id'] ?>" <?= (int)$v['color_id']===(int)$c['id']?'selected':'' ?>><?= $e($c['name']) ?></option><?php endforeach;?></select></label>
<label>SKU<input class="<?= $field ?>" name="sku" maxlength="80" required value="<?= $e($v['sku']) ?>"></label><label>Trạng thái<select class="<?= $field ?>" name="is_active"><option value="1" <?= (int)$v['is_active']===1?'selected':'' ?>>Hoạt động</option><option value="0" <?= (int)$v['is_active']===0?'selected':'' ?>>Ngừng</option></select></label><button class="<?= $btn ?>">Lưu biến thể</button></form>
<?php else:?><p><?= $e($v['sku']) ?> · <?= $e($v['color_name']) ?> · Cấu hình #<?= (int)$v['configuration_id'] ?> · <?= (int)$v['is_active']?'Hoạt động':'Ngừng' ?></p><?php endif;?></div><?php endforeach;?>
<?php if(!$configurations):?><p>Thêm cấu hình trước khi tạo biến thể.</p><?php elseif(!$variants&&!$canWrite):?><p>Chưa có biến thể.</p><?php endif;?></section>
<section class="rounded-xl border bg-white p-4"><h2 class="mb-3 text-xl font-bold">3. Hình ảnh sản phẩm</h2><div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4"><?php foreach($images as $img):?><div class="rounded border p-3"><img class="h-36 w-full object-contain" src="<?= $e($url.'/image/'.(int)$img['id']) ?>" alt="<?= $e($img['alt_text']) ?>"><p><?= $e($img['alt_text']) ?> · #<?= (int)$img['sort_order'] ?></p><?php if($canWrite):?><form method="post" action="<?= $e($url) ?>" onsubmit="return confirm('Ẩn ảnh này?')"><?php $hidden('image-remove',(int)$img['id']);?><button class="text-red-700">Xóa mềm ảnh</button></form><?php endif;?></div><?php endforeach;?></div>
<?php if($canWrite):?><form method="post" enctype="multipart/form-data" action="<?= $e($url) ?>" class="mt-4 grid gap-3 sm:grid-cols-2"><?php $hidden('image-add',0);?><label>Ảnh JPEG/PNG/WebP (tối đa 5MB)<input type="file" name="image" accept="image/jpeg,image/png,image/webp" required class="<?= $field ?>"></label><label>Mô tả ảnh<input class="<?= $field ?>" name="alt_text" maxlength="255"></label><label>Thứ tự<input class="<?= $field ?>" name="sort_order" type="number" min="0" max="100000" value="0"></label><button class="<?= $btn ?> self-end">Tải ảnh lên</button></form><?php endif;?></section>
<?php $content=ob_get_clean();require dirname(__DIR__).'/layout.php';
