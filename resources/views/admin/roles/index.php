<?php
$e = static fn(mixed $v): string => htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$allow = in_array('users.assign_roles', $navPermissions ?? [], true);
ob_start(); ?>
<div class="heading"><div><h1>Tài khoản & phân quyền</h1><p>Xem vai trò và cấp quyền cho người dùng được phép.</p></div></div>
<?php if (!empty($errors)): ?><div class="alert bad">Có lỗi dữ liệu. Vui lòng kiểm tra lại.</div><?php endif; ?>
<section class="card"><div class="tablewrap"><table><thead><tr><th>ID</th><th>Email</th><th>Vai trò hiện tại</th><th>Gán vai trò</th></tr></thead><tbody>
<?php foreach ($users as $user): ?><tr><td class="id">#<?= (int)$user['id'] ?></td><td><strong><?= $e($user['email']) ?></strong></td>
<td><?php foreach ($roles as $role): if(in_array((int)$role['id'],$user['role_ids'],true)): ?><span class="status active"><?= $e($role['name']) ?></span> <?php endif; endforeach; ?></td>
<td><?php if ($allow): ?><form method="post" action="/admin/roles/assign" class="flex"><input type="hidden" name="_csrf" value="<?= $e($csrfToken) ?>"><input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
<select name="role_id" aria-label="Chọn vai trò" required><?php foreach($roles as $role): ?><option value="<?= (int)$role['id'] ?>"><?= $e($role['name']) ?></option><?php endforeach; ?></select><button class="btn small primary" type="submit">Gán quyền</button></form>
<?php else: ?><span class="muted">Chỉ xem</span><?php endif; ?></td></tr><?php endforeach; ?>
<?php if (!$users): ?><tr><td colspan="4" class="empty">Chưa có tài khoản.</td></tr><?php endif; ?></tbody></table></div></section>
<?php if(in_array('roles.assign_permissions', $navPermissions ?? [], true)): ?>
<h2 style="font-size:18px;margin:28px 0 14px">Cấp permission cho vai trò</h2>
<section class="card"><div class="tablewrap"><table><thead><tr><th>Vai trò</th><th>Đã có quyền</th><th>Cấp quyền</th></tr></thead><tbody>
<?php foreach($roles as $role): ?><tr><td><strong><?= $e($role['name']) ?></strong></td>
<td style="white-space:normal;max-width:400px"><?php foreach($permissions as $permission): if (in_array((int)$permission['id'], $rolePermissions[$role['id']] ?? [],true)): ?><span class="status" style="display:inline-block;margin:2px"><?= $e($permission['name']) ?></span><?php endif; endforeach; ?></td>
<td><form method="post" action="/admin/roles/grant" class="flex" onsubmit="return confirm('Xác nhận cấp quyền cho vai trò này?')">
<input type="hidden" name="_csrf" value="<?= $e($csrfToken) ?>"><input type="hidden" name="role_id" value="<?= (int)$role['id'] ?>">
<select name="permission_id" aria-label="Chọn quyền"><option value="">Chọn permission</option><?php foreach($permissions as $permission): ?><option value="<?= (int)$permission['id'] ?>"><?= $e($permission['name']) ?></option><?php endforeach; ?></select><button class="btn small primary" type="submit">Cấp quyền</button></form></td>
</tr><?php endforeach; ?></tbody></table></div></section>
<?php endif; ?>
<p class="muted" style="font-size:12px;margin-top:12px">Không cho phép tự gán quyền cho tài khoản đang đăng nhập. Phân quyền được xác thực phía server.</p>
<?php $content=ob_get_clean(); require dirname(__DIR__).'/layout.php'; ?>
