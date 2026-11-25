<?php
$emptyTitle = $emptyTitle ?? 'Chưa có dữ liệu.';
$emptyHint = $emptyHint ?? 'Hãy thử lại hoặc bổ sung thông tin.';
$escapeEmpty = static fn(mixed $value): string => htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<div class="empty" role="status">
    <strong><?= $escapeEmpty($emptyTitle) ?></strong>
    <div class="muted" style="margin-top:8px"><?= $escapeEmpty($emptyHint) ?></div>
</div>
