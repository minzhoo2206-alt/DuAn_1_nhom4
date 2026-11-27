<?php
declare(strict_types=1);
// Offline smoke test: no database connection, no writes.
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require dirname(__DIR__) . '/app/Services/Admin/BrandAdminService.php';
use App\Services\Admin\BrandAdminService as Service;
$count = 0;
function check(bool $ok, string $label): void {
    global $count;
    if (!$ok) throw new RuntimeException('FAIL: ' . $label);
    ++$count; echo "[OK] $label\n";
}
function rejects(callable $fn): bool {
    try { $fn(); return false; } catch (InvalidArgumentException) { return true; }
}
check(Service::validate(['name'=>' Sony ', 'slug'=>'sony']) === ['name'=>'Sony','slug'=>'sony'], 'valid input');
foreach ([['name'=>'','slug'=>'sony'], ['name'=>str_repeat('x',101),'slug'=>'sony'], ['name'=>'Sony','slug'=>'Sony!'], ['name'=>'Sony','slug'=>str_repeat('a',121)], ['name'=>[], 'slug'=>'sony']] as $i=>$input) {
    check(rejects(fn()=>Service::validate($input)), 'reject invalid input ' . $i);
}
foreach (['activate','deactivate','delete'] as $action) {
    foreach ([false,true] as $active) foreach ([false,true] as $hasProducts) {
        $expected = ($action==='activate' && !$active) || (!$hasProducts && (($action==='deactivate' && $active) || ($action==='delete' && !$active)));
        check(!rejects(fn()=>Service::validateAction($action,['is_active'=>$active],$hasProducts)) === $expected, "$action active=".(int)$active.' products='.(int)$hasProducts);
    }
}
set_error_handler(static function(int $severity,string $message): never { throw new ErrorException($message,0,$severity); });
$root=dirname(__DIR__);
$brands=[['id'=>1,'name'=>'<script>test</script>','slug'=>'sony','is_active'=>1],['id'=>2,'name'=>'LG','slug'=>'lg','is_active'=>0]];
$brandToken='sample-token'; $success=''; $notice=''; $error=''; $brandModuleEnabled=true; $isPreview=true;
ob_start(); require $root.'/resources/views/admin/brands/database-test-list.php'; $html=ob_get_clean();
check(str_contains($html,'/admin/brands/1/deactivate') && !str_contains($html,'/admin/brands/1/delete') && str_contains($html,'/admin/brands/2/delete') && str_contains($html,'&lt;script&gt;test&lt;/script&gt;') && str_contains($html,'sample-token'), 'list buttons and escaping');
$brandId=1; $old=$brands[0];
ob_start(); require $root.'/resources/views/admin/brands/database-test-form.php'; $html=ob_get_clean();
check(str_contains($html,'/admin/brands/1/update') && str_contains($html,'sample-token') && str_contains($html,'&lt;script&gt;test&lt;/script&gt;'), 'edit form and escaping');
$brandId=0; $old=[];
ob_start(); require $root.'/resources/views/admin/brands/database-test-form.php'; $html=ob_get_clean();
check(str_contains($html,'action="/admin/brands"'), 'create form');
echo "PASS $count checks (offline only)\n";
