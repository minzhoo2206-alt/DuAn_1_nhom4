<?php
declare(strict_types=1);
/** @var string $mode */
/** @var string $email */
/** @var string $error */
/** @var string $token */
/** @var int $id */
$pageTitle=match($mode){'create'=>'Thêm nhân viên','password'=>'Đặt lại mật khẩu',default=>'Chỉnh sửa tài khoản'};
$action=match($mode){'create'=>'/admin/users','password'=>"/admin/users/$id/reset",default=>"/admin/users/$id/update"};
$e=static fn($v):string=>htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
ob_start(); ?>
<section class="max-w-xl"><a href="/admin/users" class="text-blue-600">← Danh sách tài khoản</a><h1 class="my-4 text-2xl font-bold"><?= $e($pageTitle) ?></h1>
<p class="mb-4 bg-blue-50 p-3 text-blue-700">Chỉ sử dụng email và mật khẩu giả lập. Không dùng mật khẩu thật.</p>
<?php if($error!==''): ?><p role="alert" class="mb-3 rounded bg-red-50 p-3 text-red-700"><?= $e($error) ?></p><?php endif; ?>
<form method="post" action="<?= $e($action) ?>" class="space-y-4 rounded-xl border bg-white p-5">
<input type="hidden" name="user_token" value="<?= $e($token) ?>">
<?php if($mode!=='password'): ?><label class="block">Email<input required type="email" name="email" maxlength="254" value="<?= $e($email) ?>" class="mt-1 block w-full border rounded p-2" autocomplete="off"></label><?php else: ?><p>Email: <?= $e($email) ?></p><?php endif; ?>
<?php if($mode!=='edit'): ?><label class="block">Mật khẩu mới (12–72 byte)<input required type="password" name="password" minlength="12" maxlength="72" autocomplete="new-password" class="mt-1 block w-full border rounded p-2"></label><?php endif; ?>
<?php if($mode==='create'): ?><p class="text-sm text-slate-500">Vai trò cố định: nhân viên kiểm thử. Không có quyền Admin thật.</p><?php endif; ?>
<button class="rounded bg-blue-600 px-4 py-2 text-white">Lưu</button></form></section>
<?php $content=ob_get_clean(); require dirname(__DIR__).'/layout.php'; ?>
