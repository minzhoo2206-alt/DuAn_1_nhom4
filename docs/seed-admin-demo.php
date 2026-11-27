<?php
declare(strict_types=1);
use App\Database\Seeders\AdminDemoSeeder;
use Dotenv\Dotenv;
if(PHP_SAPI!=='cli'){http_response_code(403);exit('CLI only');}
$root=dirname(__DIR__);$stage='OFFLINE CHECK';
try {
    require $root.'/app/Services/Admin/OrderAdminService.php';
    require $root.'/database/seeders/AdminDemoSeeder.php';
    $data=AdminDemoSeeder::data(); AdminDemoSeeder::validate($data);
    $mode=$argv[1]??'--check';
    if(!in_array($mode,['--check','--apply'],true)||$argc>2){echo "Usage: php docs/seed-admin-demo.php --check|--apply\n";exit(1);}
    foreach(['brands','categories','products','customers','orders'] as $key) echo "[OK] $key: ".count($data[$key])." fixtures\n";
    if($mode==='--check'){echo "PASS TASK 19 OFFLINE CHECK (no database changes)\n";exit(0);}
    $stage='ENV';
    require $root.'/vendor/autoload.php';
    // Isolated array: never load .env or consume inherited DB_* variables.
    $env=Dotenv::createArrayBacked($root,'.env.admin-test')->load();
    if(($env['DB_HOST']??null)!=='127.0.0.1'||($env['DB_DATABASE']??null)!=='fourtech_admin_test'||($env['DB_CHARSET']??null)!=='utf8mb4') throw new RuntimeException('Blocked config');
    $port=filter_var($env['DB_PORT']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1,'max_range'=>65535]]);
    if($port===false||empty($env['DB_USERNAME'])||!array_key_exists('DB_PASSWORD',$env))throw new RuntimeException('Missing config');
    $stage='MYSQL LOGIN';
    $db=new PDO('mysql:host=127.0.0.1;port='.$port.';dbname=fourtech_admin_test;charset=utf8mb4',$env['DB_USERNAME'],$env['DB_PASSWORD'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false,PDO::ATTR_TIMEOUT=>5]);
    $stage='SEED TRANSACTION';
    $stats=(new AdminDemoSeeder($db))->run($data);
    foreach($stats as $key=>$s) echo "$key: inserted={$s['inserted']} existing={$s['existing']} skipped={$s['skipped']}\n";
    echo "PASS TASK 19 SEED (local only)\n";
}catch(PDOException $e){echo "FAIL STEP: $stage\nSQLSTATE: ".$e->getCode()."\nMYSQL ERROR: ".($e->errorInfo[1]??'unknown')."\n";exit(1);}
catch(Throwable $e){echo "FAIL STEP: $stage\nERROR TYPE: ".get_class($e)."\n";if($stage==='SEED TRANSACTION' && $e instanceof RuntimeException)echo $e->getMessage()."\n";exit(1);}
