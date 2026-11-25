<?php
$e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?><!doctype html><html lang="vi"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Lỗi <?= (int) ($status ?? 500) ?> | FOURTECH</title>
<body style="background:#f5f7fb;font-family:system-ui;margin:0;display:grid;min-height:100vh;place-items:center;color:#1b2840">
<main style="background:white;border:1px solid #e3e8f2;border-radius:18px;max-width:520px;padding:44px;text-align:center"><div style="font-size:56px;font-weight:900;color:#315df0"><?= (int) ($status ?? 500) ?></div>
<h1>Không thể thực hiện yêu cầu</h1><p><?= $e($message ?? 'Có lỗi xảy ra.') ?></p><a style="color:#315df0" href="/">Trở về trang chủ</a></main></body></html>
