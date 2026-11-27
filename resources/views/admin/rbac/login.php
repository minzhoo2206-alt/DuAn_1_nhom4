<?php
declare(strict_types=1);
/** @var string $token */
/** @var string $error */
$e=static fn($v)=>htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
?>
<!doctype html><html lang="vi"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>FOURTECH · Đăng nhập kiểm thử</title>
<style>body{font:16px system-ui;background:#f1f5f9;color:#0f172a;margin:0}main{max-width:420px;margin:8vh auto;background:white;padding:32px;border-radius:16px;box-shadow:0 8px 30px #0f172a15}label,input,button{display:block;width:100%;box-sizing:border-box}input,button{padding:12px;margin:8px 0 18px;border:1px solid #cbd5e1;border-radius:8px}button{background:#2563eb;color:white;font-weight:bold;cursor:pointer}.error{color:#b91c1c}</style>
<main><h1>FOURTECH Admin</h1><p>Task 17 · Đăng nhập local bằng tài khoản @example.test.</p>
<?php if($error!==''): ?><p class="error"><?= $e($error) ?></p><?php endif; ?>
<form method="post" action="/admin/login"><input type="hidden" name="rbac_token" value="<?= $e($token) ?>"><label>Email<input name="email" type="email" required autocomplete="username"></label><label>Mật khẩu<input name="password" type="password" required autocomplete="current-password"></label><button>Đăng nhập</button></form></main></html>
