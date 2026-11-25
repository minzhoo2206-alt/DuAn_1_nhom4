<?php
$e = static fn(mixed $v): string => htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$title = $pageTitle ?? 'Quản trị';
$perms = $navPermissions ?? [];
$menu = [
    ['dashboard.view', '/admin', '▦', 'Tổng quan'],
    ['products.view', '/admin/products', '▤', 'Sản phẩm'],
    ['categories.view', '/admin/categories', '◈', 'Danh mục'],
    ['brands.view', '/admin/brands', '◇', 'Thương hiệu'],
    ['orders.view', '/admin/orders', '▣', 'Đơn hàng'],
    ['users.view', '/admin/roles', '♙', 'Tài khoản & quyền'],
];
?>
<!doctype html>
<html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $e($title) ?> · FOURTECH Admin</title>
<style>
:root{--navy:#101c30;--blue:#285ef0;--bg:#f5f7fb;--line:#e5eaf2;--text:#17233a;--muted:#718097}
*{box-sizing:border-box}body{margin:0;background:var(--bg);font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",Arial,sans-serif;color:var(--text)}a{text-decoration:none;color:inherit}
.shell{display:flex;min-height:100vh}.side{width:242px;flex-shrink:0;background:var(--navy);color:#c5d2e5;padding:30px 16px;position:sticky;top:0;height:100vh}
.brand{display:flex;gap:10px;align-items:center;color:#fff;font-size:20px;font-weight:900;letter-spacing:.3px;padding:5px 12px 28px}.brandmark{background:#396bff;padding:8px 11px;border-radius:12px}
.group-title{color:#8395b4;font-weight:750;font-size:10px;letter-spacing:1.3px;padding:20px 13px 9px}.nav{display:flex;align-items:center;gap:12px;padding:12px 13px;margin:4px 0;border-radius:11px;font-size:13px;font-weight:650}
.nav:hover,.nav:focus{background:#263754;color:#fff}.nav.active{background:#335ae8;color:white}.nav .sym{width:21px;font-size:17px;text-align:center}
.panel{min-width:0;flex:1}.top{background:#fff;min-height:77px;display:flex;align-items:center;justify-content:space-between;padding:18px 33px;border-bottom:1px solid var(--line)}
.top small{font-size:12px;color:var(--muted)}.top strong{font-size:16px}.avatar{background:#e8eeff;border-radius:50%;width:36px;height:36px;display:grid;place-items:center;color:#315be1;font-weight:900}
.wrap{padding:30px 33px;max-width:1500px;margin:auto}.heading{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:0 0 22px}.heading h1{font-size:25px;letter-spacing:-.7px;margin:0}.heading p{font-size:13px;color:var(--muted);margin:7px 0 0}
.card{background:white;border:1px solid var(--line);border-radius:15px;box-shadow:0 5px 20px rgba(26,46,90,.025)}.card-pad{padding:22px}.toolbar{display:flex;gap:12px;align-items:center;flex-wrap:wrap;padding:18px 20px;border-bottom:1px solid var(--line)}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;cursor:pointer;border:1px solid var(--line);font:700 13px inherit;font-size:13px;font-weight:700;border-radius:9px;padding:10px 15px;background:#fff;color:#263450;min-height:39px}
.btn.primary{color:white;border-color:var(--blue);background:var(--blue)}.btn.primary:hover{background:#1c4adb}.btn.warn{color:#9a3d32;background:#fff8f7}.btn.small{min-height:32px;padding:6px 10px;font-size:12px}
input,select{padding:10px 12px;border:1px solid #dce4ef;border-radius:8px;outline:none;font:inherit;font-size:13px;background:#fff;max-width:100%;color:var(--text)}input:focus,select:focus{border-color:var(--blue);box-shadow:0 0 0 3px #285ef01b}
.input{width:100%}.search{min-width:230px}label{display:block;font-size:13px;font-weight:700;margin-bottom:8px}.field{margin-bottom:20px}.helper{font-size:12px;color:var(--muted);margin-top:7px}.error{color:#bc3434;font-size:12px;margin-top:7px}
.tablewrap{overflow-x:auto}table{border-collapse:collapse;width:100%;text-align:left;white-space:nowrap;font-size:13px}th{font-size:11px;color:#7c8a9f;text-transform:uppercase;letter-spacing:.6px;background:#f8fafd;padding:15px 20px}td{padding:16px 20px;border-top:1px solid #eef1f7}tr:hover td{background:#fcfdff}
.id{color:#8591a4}.muted{color:var(--muted)}.status{font-size:11px;font-weight:800;padding:5px 10px;border-radius:30px;background:#f3f4f6;color:#526176}.status.active{background:#e8f8f0;color:#168453}.status.draft{background:#fff4dd;color:#a56b12}
.actions{display:flex;gap:7px;align-items:center}.link{color:#285ef0;font-weight:750}.pagination{display:flex;justify-content:space-between;gap:15px;align-items:center;padding:16px 20px;border-top:1px solid var(--line);font-size:12px;color:var(--muted)}
.alert{padding:14px 17px;border-radius:10px;background:#eaf8ef;color:#146d45;border:1px solid #c9ecda;margin-bottom:19px;font-size:13px;font-weight:650}.alert.bad{background:#fff0f0;color:#ad3434;border-color:#ffdddd}
.stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px;margin:15px 0 23px}.stat{padding:25px}.stat .value{font-size:34px;letter-spacing:-1.5px;font-weight:850;margin:10px 0}.stat .muted{font-size:13px}
.empty{padding:45px 16px;text-align:center;color:var(--muted)}.form-card{max-width:750px}.crumb{font-size:12px;color:var(--muted);margin-bottom:14px}.flex{display:flex;gap:12px;align-items:center;flex-wrap:wrap}
@media(max-width:900px){.side{width:72px;padding:25px 9px}.brand{justify-content:center;padding:0 0 30px}.brand span:last-child,.nav .caption,.group-title{display:none}.nav{justify-content:center;padding:13px 4px}.nav .sym{font-size:23px}.wrap{padding:22px}.top{padding:14px 22px}}
@media(max-width:600px){.shell{display:block}.side{width:100%;height:auto;position:static;display:flex;overflow-x:auto;padding:10px}.brand{font-size:13px;padding:5px}.brandmark{padding:7px}.nav{margin:0;min-width:38px}.top{padding:15px}.wrap{padding:16px}.heading h1{font-size:22px}.heading{align-items:start}.stats{grid-template-columns:1fr}.search{min-width:160px}.card-pad{padding:18px}}
</style></head>
<body><div class="shell"><aside class="side"><a class="brand" href="/admin"><span class="brandmark">F</span><span>FOURTECH<span style="color:#86a8ff">.</span></span></a>
<div class="group-title">QUẢN TRỊ HỆ THỐNG</div>
<nav aria-label="Menu quản trị">
<?php foreach ($menu as [$permission, $href, $sym, $caption]): ?>
<?php if (in_array($permission, $perms, true)): ?>
<a class="nav <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '/', $href === '/admin' ? '/admin?' : $href) ? 'active' : '' ?>" href="<?= $e($href) ?>" aria-label="<?= $e($caption) ?>"><span class="sym" aria-hidden="true"><?= $sym ?></span><span class="caption"><?= $e($caption) ?></span></a>
<?php endif; endforeach; ?></nav>
</aside><div class="panel"><header class="top"><div><small>Hệ thống / Quản trị</small><div><strong><?= $e($title) ?></strong></div></div><div class="flex"><a class="muted" href="/" style="font-size:13px">Về cửa hàng</a><span class="avatar" aria-label="Tài khoản quản trị">A</span></div></header>
<main class="wrap">
<?php if (!empty($flash)): ?><div class="alert" role="status"><?= $e($flash) ?></div><?php endif; ?>
<?= $content ?? '' ?>
</main></div></div></body></html>
