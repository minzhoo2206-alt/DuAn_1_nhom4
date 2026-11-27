<?php
declare(strict_types=1);
// CLI-only QA runner. Default: offline. --db: local read-only SQL and repository checks.
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
$root = dirname(__DIR__);
$mode = $argv[1] ?? '--offline';
if ($argc > 2 || !in_array($mode, ['--offline','--db'], true)) { echo "Usage: php docs/check-admin-task20.php --offline|--db\n"; exit(1); }
$fail = 0; $passed = 0;
function result(bool $ok, string $label): void {
    global $fail, $passed;
    if ($ok) ++$passed; else ++$fail;
    echo ($ok ? '[OK] ' : '[FAIL] ').$label.PHP_EOL;
}
function need(bool $ok, string $label): void {
    result($ok, $label); if (!$ok) throw new RuntimeException('Prerequisite failed');
}
if ($mode === '--offline') {
    $files = [];
    foreach (['app/Controllers/Admin','app/Repositories/Admin','app/Services/Admin','resources/views/admin','database/seeders'] as $dir) {
        if (!is_dir($root.'/'.$dir)) { result(false, 'Missing '.$dir); continue; }
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/'.$dir, FilesystemIterator::SKIP_DOTS)) as $f) {
            if ($f->isFile() && $f->getExtension()==='php') $files[]=$f->getPathname();
        }
    }
    $tests = [
        'docs/check-admin-brand-rules.php'=>['PASS 21 checks (offline only)',[]],
        'docs/check-admin-user-rules.php'=>['PASS 18 checks (offline only)',[]],
        'docs/check-admin-rbac-rules.php'=>['PASS 41 checks (offline only)',[]],
        'docs/check-admin-order-rules.php'=>['PASS 33 checks (offline only)',[]],
        'docs/seed-admin-demo.php'=>['PASS TASK 19 OFFLINE CHECK (no database changes)',['--check']],
        'docs/check-admin-catalog-rules.php'=>['PASS 18 catalog checks (offline only)',[]],
    ];
    foreach (array_merge(array_keys($tests), ['docs/admin-db-test.php','docs/admin-db-test-brands.php','docs/admin-db-test-categories.php','docs/check-admin-task20.php']) as $file) $files[]=$root.'/'.$file;
    $files = array_unique($files); sort($files);
    $linted = 0;
    foreach ($files as $file) {
        if (!is_file($file)) { result(false, 'Missing '.substr($file,strlen($root)+1)); continue; }
        $out=[]; exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($file).' 2>&1',$out,$code);
        if ($code===0) ++$linted; else { result(false,'PHP lint '.substr($file,strlen($root)+1)); echo implode(PHP_EOL,$out).PHP_EOL; }
    }
    result($linted===count($files), 'PHP lint '.$linted.'/'.count($files));
    foreach ($tests as $file=>[$marker,$args]) {
        if (!is_file($root.'/'.$file)) {result(false,'Missing '.$file);continue;}
        $out=[]; $cmd=escapeshellarg(PHP_BINARY).' '.escapeshellarg($root.'/'.$file);
        foreach($args as $arg) $cmd.=' '.escapeshellarg($arg);
        exec($cmd.' 2>&1',$out,$code);
        $ok=$code===0 && in_array($marker,$out,true);
        result($ok,$file.($ok?' — '.$marker:''));
        if(!$ok) echo implode(PHP_EOL,$out).PHP_EOL;
    }
} else {
    $stage='PREREQUISITES'; $db=null;
    try {
        need(is_file($root.'/vendor/autoload.php'),'Composer autoload exists');
        need(is_file($root.'/.env.admin-test'),'Local env exists');
        need(in_array('mysql',PDO::getAvailableDrivers(),true),'PDO MySQL driver');
        require $root.'/vendor/autoload.php';
        $stage='LOCAL CONFIG';
        $env=\Dotenv\Dotenv::createArrayBacked($root,'.env.admin-test')->load();
        $port=filter_var($env['DB_PORT']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1,'max_range'=>65535]]);
        need(($env['DB_HOST']??null)==='127.0.0.1' && ($env['DB_DATABASE']??null)==='fourtech_admin_test' && ($env['DB_CHARSET']??null)==='utf8mb4' && $port!==false && !empty($env['DB_USERNAME']) && array_key_exists('DB_PASSWORD',$env),'Isolated local configuration');
        $stage='MYSQL LOGIN';
        $db=new PDO('mysql:host=127.0.0.1;port='.$port.';dbname=fourtech_admin_test;charset=utf8mb4',$env['DB_USERNAME'],$env['DB_PASSWORD'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false,PDO::ATTR_TIMEOUT=>5]);
        need($db->query('SELECT DATABASE()')->fetchColumn()==='fourtech_admin_test','Connected to local test database');
        $db->exec('SET TRANSACTION READ ONLY'); $db->beginTransaction();
        $stage='SCHEMA';
        foreach(['brands','categories','products','users','roles','user_has_roles','permissions','role_has_permissions','orders','order_items','order_status_history'] as $t) {
            $db->query("SELECT 1 FROM `$t` LIMIT 0"); result(true,'Table '.$t);
        }
        $stage='FIXTURES';
        $queries=[
            ['products',"SELECT COUNT(*) FROM products WHERE slug LIKE 'task19-phone-%' AND deleted_at IS NULL",100],
            ['customers',"SELECT COUNT(*) FROM users WHERE email LIKE 'task19-customer-%@example.test' AND deleted_at IS NULL",3],
            ['Task18 orders',"SELECT COUNT(*) FROM orders WHERE checkout_key LIKE 'task18-demo-%' AND deleted_at IS NULL",2],
            ['Task19 orders',"SELECT COUNT(*) FROM orders WHERE checkout_key LIKE 'task19-demo-%' AND deleted_at IS NULL",8],
        ];
        foreach($queries as [$label,$sql,$expected]) { $n=(int)$db->query($sql)->fetchColumn();result($n===$expected,"$label: $n/$expected (unedited fixtures expected)"); }
        foreach(['brands'=>10,'categories'=>20] as $t=>$expected){$n=(int)$db->query("SELECT COUNT(*) FROM `$t` WHERE is_active=1 AND deleted_at IS NULL")->fetchColumn();result($n>=$expected,"Active $t: $n (expected at least $expected)");}
        $stage='REPOSITORIES';
        $product=new \App\Services\Admin\ProductAdminService(new \App\Repositories\Admin\ProductAdminRepository($db));
        $list=$product->listing(1,10); $options=$product->formOptions();
        result(count($list['products'])<=10 && $list['total']>=count($list['products']) && count($options['brands'])>0 && count($options['categories'])>0,'Product listing / pagination / options');
        result(is_array((new \App\Repositories\Admin\BrandAdminRepository($db))->all()),'Brand listing');
        result(is_array((new \App\Repositories\Admin\CategoryAdminRepository($db))->all()),'Category listing');
        result(is_array((new \App\Services\Admin\UserAdminService(new \App\Repositories\Admin\UserAdminRepository($db)))->listing('','',1)),'User listing');
        $stage='RBAC';
        $rbac=new \App\Repositories\Admin\RbacAdminRepository($db);
        $s=$db->prepare('SELECT id FROM users WHERE email=? AND deleted_at IS NULL');$s->execute(['admin17@example.test']);$id=$s->fetchColumn();
        $actor=$id===false?null:$rbac->actor((int)$id);
        $rules=\App\Services\Admin\RbacAdminService::class;
        $ok=($actor['role']??'')===$rules::ADMIN;
        foreach([$rules::READ,$rules::WRITE,$rules::USERS,$rules::RBAC,$rules::ORDERS] as $permission) $ok=$ok && $rules::allowed($actor,$permission);
        result($ok,'Test admin role + five permissions (no password printed)');
        $stage='ORDER LIST / DETAIL';
        $repo=new \App\Repositories\Admin\OrderAdminRepository($db);
        $ids=$db->query("SELECT id,checkout_key FROM orders WHERE (checkout_key LIKE 'task18-demo-%' OR checkout_key LIKE 'task19-demo-%') AND deleted_at IS NULL ORDER BY id")->fetchAll();
        $list=$repo->listing('','',1); result($list['total']===count($ids),'Order repository lists both fixture groups');
        $detailOK=count($ids)>0;
        foreach($ids as $row){$d=$repo->detail((int)$row['id']);$detailOK=$detailOK && $d['order']['checkout_key']===$row['checkout_key'] && count($d['items'])>0;}
        result($detailOK,'All local fixture order details have items');
        $db->rollBack();
    } catch (Throwable $e) {
        if($db instanceof PDO && $db->inTransaction()) $db->rollBack();
        result(false,'Stopped at '.$stage.' ('.get_class($e).')');
        if($e instanceof PDOException) echo 'SQLSTATE: '.$e->getCode().' / MYSQL ERROR: '.($e->errorInfo[1]??'unknown').PHP_EOL;
    }
}
echo "SUMMARY: $passed passed, $fail failed".PHP_EOL;
echo ($fail===0 ? 'PASS' : 'FAIL').' TASK 20 '.($mode==='--db'?'LOCAL READ CHECK':'OFFLINE CHECK').PHP_EOL;
exit($fail===0?0:1);
