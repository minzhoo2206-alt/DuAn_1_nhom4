<?php

declare(strict_types=1);

use App\Core\Database;
use Dotenv\Dotenv;

// Chỉ cho phép chạy trong Terminal.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Forbidden');
}

$root = dirname(__DIR__);

require $root . '/vendor/autoload.php';

try {
    // Đọc thông tin trong file .env
    $dotenv = Dotenv::createImmutable($root);
    $dotenv->load();

    $dotenv->required([
        'DB_HOST',
        'DB_DATABASE',
        'DB_USERNAME',
        'DB_PASSWORD'
    ])->notEmpty();

    // Sử dụng Core của Minh
    $db = Database::connection();

    echo "KET NOI MYSQL THANH CONG!\n\n";

    // Kiểm tra database hiện tại
    $database = $db->query(
        'SELECT DATABASE()'
    )->fetchColumn();

    echo "Database: " . $database . "\n";

    // Đếm số bảng
    $tables = $db->query(
        'SHOW TABLES'
    )->fetchAll(PDO::FETCH_COLUMN);

    echo "Tong so bang: " . count($tables) . "\n";

    // Đếm số sản phẩm
    $total = $db->query(
        'SELECT COUNT(*) FROM products'
    )->fetchColumn();

    echo "Tong so san pham: " . $total . "\n\n";

    // Đọc danh sách sản phẩm
    $products = $db->query(
        'SELECT id, name, status
         FROM products
         WHERE deleted_at IS NULL
         ORDER BY id DESC
         LIMIT 10'
    )->fetchAll();

    echo "DANH SACH SAN PHAM:\n\n";

    foreach ($products as $product) {
        echo $product['id'] . ' | '
            . $product['name'] . ' | '
            . $product['status'] . "\n";
    }

} catch (Throwable $e) {

    echo "KIEM TRA THAT BAI!\n";
    echo $e->getMessage() . "\n";
    exit(1);
}
