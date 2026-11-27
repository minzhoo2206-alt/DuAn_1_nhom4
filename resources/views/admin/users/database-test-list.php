<?php
declare(strict_types=1);
/** @var array $data */
/** @var string $token */
/** @var string $message */
use App\Services\Admin\UserAdminService;
$pageTitle='Quản lý tài khoản';
$e=static fn($v):string=>htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
$labels=['active'=>'Hoạt động','locked'=>'Đã khóa','pending_verification'=>'Chờ xác minh','disabled'=>'Vô hiệu'];
ob_start(); ?>
<section>
<h1 class="text-2xl font-bold">Quản lý tài khoản</h1>
<p class="my-3 rounded-lg bg-blue-50 p-3 text-blue-700">MySQL local · chỉ sửa nhân viên kiểm thử. Không dùng tài khoản thật.</p>
<?php if($message!==''): ?><p class="p-3 bg-green-50 text-green-700"><?= $e($message) ?></p><?php endif; ?>
<div class="my-4 flex flex-wrap gap-3 justify-between">
<form method="get" action="/admin/users" class="flex flex-wrap gap-2">
<input name="q" value="<?= $e($data['q']) ?>" placeholder="Tìm email" class="border rounded p-2">
<select name="status" class="border rounded p-2"><option value="">Mọi trạng thái</option><?php foreach($labels as $key=>$label): ?><option value="<?= $e($key) ?>" <?= $data['status']===$key?'selected':'' ?>><?= $e($label) ?></option><?php endforeach; ?></select>
<button class="border rounded p-2">Tìm kiếm</button></form>
<a href="/admin/users/create" class="rounded bg-blue-600 px-4 py-2 text-white">+ Thêm nhân viên test</a></div>
<div class="overflow-x-auto rounded-xl border bg-white"><table class="w-full text-left text-sm"><thead class="bg-slate-100"><tr><th class="p-3">ID</th><th class="p-3">Email</th><th class="p-3">Vai trò</th><th class="p-3">Trạng thái</th><th class="p-3">Thao tác</th></tr></thead><tbody>
<?php foreach($data['users'] as $row): $id=(int)$row['id']; ?>
<tr class="border-t"><td class="p-3"><?= $id ?></td><td class="p-3"><?= $e($row['email']) ?></td><td class="p-3"><?= $e($row['role_codes']??'Chưa có') ?></td><td class="p-3"><?= $e($labels[$row['status']]??$row['status']) ?></td><td class="p-3">
<?php if(UserAdminService::manageable($row)): $action=$row['status']==='active'?'lock':'unlock'; ?>
<div class="flex flex-wrap gap-3"><a class="text-blue-600" href="/admin/users/<?= $id ?>/edit">Sửa</a><a class="text-blue-600" href="/admin/users/<?= $id ?>/password">Đặt lại mật khẩu</a>
<form method="post" action="/admin/users/<?= $id ?>/<?= $action ?>" onsubmit="return confirm('Xác nhận thao tác?')"><input type="hidden" name="user_token" value="<?= $e($token) ?>"><button class="text-red-600"><?= $action==='lock'?'Khóa':'Mở khóa' ?></button></form></div>
<?php else: ?><span class="text-slate-500">Được bảo vệ</span><?php endif; ?></td></tr>
<?php endforeach; if(!$data['users']): ?><tr><td colspan="5" class="p-6 text-center">Chưa có tài khoản phù hợp.</td></tr><?php endif; ?></tbody></table></div>
<div class="mt-4 flex flex-wrap gap-3 items-center"><span><?= (int)$data['total'] ?> tài khoản · Trang <?= (int)$data['page'] ?>/<?= (int)$data['pages'] ?></span>
<?php foreach(['Trước'=>$data['page']-1,'Sau'=>$data['page']+1] as $label=>$p): if($p>=1&&$p<=$data['pages']): ?><a class="text-blue-600" href="/admin/users?<?= $e(http_build_query(['q'=>$data['q'],'status'=>$data['status'],'page'=>$p])) ?>"><?= $e($label) ?></a><?php endif; endforeach; ?></div>
</section>
<?php $content=ob_get_clean(); require dirname(__DIR__).'/layout.php'; ?>
