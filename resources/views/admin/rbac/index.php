<?php
declare(strict_types=1);
/** @var array $accounts */
/** @var array $matrix */
/** @var array $actor */
/** @var string $token */
/** @var string $message */
/** @var int $page */
use App\Services\Admin\RbacAdminService as Rules;
$pageTitle='Phân quyền RBAC';$e=static fn($v)=>htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
$roles=[Rules::ADMIN=>'Admin',Rules::STAFF=>'Nhân viên',Rules::CUSTOMER=>'Khách hàng'];
$permissions=[Rules::READ=>'Xem danh mục hàng hóa',Rules::WRITE=>'Thêm, sửa, ẩn, xóa mềm hàng hóa',Rules::USERS=>'Quản lý tài khoản',Rules::RBAC=>'Quản lý phân quyền'];
$grants=[];foreach($matrix as $item)$grants[$item['role_code']][$item['permission_code']]=true;
ob_start(); ?>
<h1 class="text-2xl font-bold">Phân quyền RBAC</h1><p class="my-3 bg-blue-50 text-blue-800 rounded p-3">Local only · Admin bảo vệ cố định; chỉ chuyển vai trò nhân viên ↔ khách hàng kiểm thử.</p>
<?php if($message!==''): ?><p class="bg-green-50 text-green-700 p-3"><?= $e($message) ?></p><?php endif; ?>
<div class="overflow-x-auto rounded border bg-white my-4"><table class="w-full text-sm text-left"><thead><tr><th class="p-3">Quyền</th><?php foreach($roles as $label): ?><th class="p-3"><?= $e($label) ?></th><?php endforeach; ?></tr></thead><tbody>
<?php foreach($permissions as $code=>$label): ?><tr class="border-t"><td class="p-3"><?= $e($label) ?></td><?php foreach($roles as $role=>$name): ?><td class="p-3"><?= Rules::allowed(['status'=>'active','role'=>$role,'permissions'=>array_keys($grants[$role]??[])],$code)?'Có':'Không' ?></td><?php endforeach; ?></tr><?php endforeach; ?></tbody></table></div>
<form method="post" action="/admin/rbac/staff-write" class="flex flex-wrap gap-3 my-4"><input type="hidden" name="rbac_token" value="<?= $e($token) ?>"><button name="allow" value="1" class="bg-blue-600 text-white px-4 py-2 rounded">Cấp quyền sửa hàng hóa cho nhân viên</button><button name="allow" value="0" class="border px-4 py-2 rounded">Thu hồi quyền sửa</button></form>
<h2 class="font-bold text-xl my-4">Vai trò tài khoản kiểm thử</h2><div class="overflow-x-auto bg-white border rounded"><table class="w-full text-left text-sm"><thead><tr><th class="p-3">Email</th><th class="p-3">Trạng thái</th><th class="p-3">Vai trò</th><th class="p-3">Thay đổi</th></tr></thead><tbody>
<?php foreach($accounts as $u): ?><tr class="border-t"><td class="p-3"><?= $e($u['email']) ?></td><td class="p-3"><?= $e($u['status']) ?></td><td class="p-3"><?= $e($roles[$u['role_codes']??'']??'Được bảo vệ') ?></td><td class="p-3">
<?php if(in_array($u['role_codes'],[Rules::STAFF,Rules::CUSTOMER],true) && in_array($u['status'],['active','locked'],true) && (int)$u['id']!==(int)$actor['id']): ?><form class="flex gap-2" method="post" action="/admin/rbac/role"><input type="hidden" name="rbac_token" value="<?= $e($token) ?>"><input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>"><select name="role" class="border rounded p-2"><?php foreach([Rules::STAFF,Rules::CUSTOMER] as $role): ?><option value="<?= $e($role) ?>" <?= $u['role_codes']===$role?'selected':'' ?>><?= $e($roles[$role]) ?></option><?php endforeach; ?></select><button class="bg-blue-600 text-white rounded px-3">Lưu</button></form><?php else: ?>Được bảo vệ<?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div>
<div class="flex gap-4 my-3"><?php if($page>1): ?><a href="?page=<?= $page-1 ?>">Trang trước</a><?php endif; ?><span>Trang <?= $page ?></span><?php if(count($accounts)===50&&$page<10000): ?><a href="?page=<?= $page+1 ?>">Trang sau</a><?php endif; ?></div>
<?php $content=ob_get_clean();require dirname(__DIR__).'/layout.php'; ?>
