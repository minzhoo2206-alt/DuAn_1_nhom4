<?php

declare(strict_types=1);

use App\Repositories\Admin\ProductAdminRepository;
use App\Services\Admin\ProductAdminService;
use Dotenv\Dotenv;

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}
$root = dirname(__DIR__);
$stage = 'FILES';
try {
    foreach (['vendor/autoload.php', '.env.admin-test', 'config/database.php'] as $file) {
        if (!is_file($root . '/' . $file)) {
            echo "THIEU FILE: $file\n";
            exit(1);
        }
    }
    echo "[OK] Files\n";
    $stage = 'ENV';
    require $root . '/vendor/autoload.php';
    $dotenv = Dotenv::createImmutable($root, '.env.admin-test');
    $dotenv->load();
    $dotenv->required(['DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_CHARSET'])->notEmpty();
    $dotenv->required('DB_PASSWORD');
    $config = require $root . '/config/database.php';
    if ($config['host'] !== '127.0.0.1' || $config['database'] !== 'fourtech_admin_test') {
        echo "BLOCKED: DB_HOST must be 127.0.0.1 and DB_DATABASE must be fourtech_admin_test.\n";
        exit(1);
    }
    echo "[OK] Local config: fourtech_admin_test, port " . (int) $config['port'] . "\n";
    $stage = 'MYSQL LOGIN';
    // Same DSN as Core; direct PDO here only to report the driver error code.
    $db = new PDO(sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $config['host'], $config['port'], $config['database'], $config['charset']), $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_TIMEOUT => 5]);
    echo "[OK] MySQL login\n";
    $stage = 'SCHEMA';
    if ($db->query('SELECT DATABASE()')->fetchColumn() !== 'fourtech_admin_test') {
        exit("BLOCKED: wrong database\n");
    }
    foreach (['brands', 'categories', 'products'] as $table) {
        $total = $db->query('SELECT COUNT(*) FROM `' . $table . '`')->fetchColumn();
        echo "[OK] $table: $total rows\n";
    }
    $stage = 'REPOSITORY / SERVICE';
    $service = new ProductAdminService(new ProductAdminRepository($db));
    $list = $service->listing(1, 10);
    $options = $service->formOptions();
    echo '[OK] Product listing: ' . $list['total'] . "\n";
    echo '[OK] Active brands: ' . count($options['brands']) . "\n";
    echo '[OK] Active categories: ' . count($options['categories']) . "\n";
    echo "TASK 14.3 READ CHECK OK\n";
} catch (PDOException $e) {
    echo "FAIL STEP: $stage\n";
    echo 'SQLSTATE: ' . $e->getCode() . "\n";
    echo 'MYSQL ERROR: ' . ($e->errorInfo[1] ?? 'unknown') . "\n";
    exit(1);
} catch (Throwable $e) {
    echo "FAIL STEP: $stage\n";
    echo 'ERROR TYPE: ' . get_class($e) . "\n";
    // Do not print raw exception text: environment parsing errors may contain secrets.
    exit(1);
}
