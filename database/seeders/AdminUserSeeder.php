<?php
declare(strict_types=1);
namespace Database\Seeders;

use PDO;
use RuntimeException;

/** Không chứa password demo mặc định. Bắt buộc đưa qua ENV, không commit thông tin nhạy cảm. */
final class AdminUserSeeder
{
    public function run(PDO $db): void
    {
        $env = $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'production';
        if (!in_array($env, ['local','testing','test'], true)) {
            throw new RuntimeException('Cấm tạo admin demo ngoài môi trường local/test.');
        }
        $email = trim((string) ($_ENV['ADMIN_DEMO_EMAIL'] ?? getenv('ADMIN_DEMO_EMAIL') ?: ''));
        $password = (string) ($_ENV['ADMIN_DEMO_PASSWORD'] ?? getenv('ADMIN_DEMO_PASSWORD') ?: '');
        if ($email === '' && $password === '') { return; } // Không seed khi không cấu hình.
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 12) {
            throw new RuntimeException('ADMIN_DEMO_EMAIL hợp lệ và ADMIN_DEMO_PASSWORD tối thiểu 12 ký tự.');
        }
        // Cấu trúc users thuộc Nhuận. Chỉ chạy nếu cột email, name, password tồn tại.
        $columns = $db->query('SHOW COLUMNS FROM users')->fetchAll(PDO::FETCH_COLUMN);
        $passwordCol = in_array('password_hash', $columns, true) ? 'password_hash' : (in_array('password', $columns, true) ? 'password' : null);
        if (!in_array('email', $columns, true) || $passwordCol === null) {
            throw new RuntimeException('DDL users chưa tương thích với AdminUserSeeder.');
        }
        $stmt = $db->prepare('SELECT id FROM users WHERE email=:email LIMIT 1');
        $stmt->execute(['email'=>$email]);
        $id = $stmt->fetchColumn();
        if ($id !== false) { return; } // Idempotent, không reset password khi chạy lại.
        $cols = ['email', $passwordCol]; $params = ['email'=>$email, 'hashed'=>password_hash($password, PASSWORD_DEFAULT)];
        if (in_array('name', $columns, true)) { $cols[] = 'name'; $params['name'] = 'FOURTECH Admin Demo'; }
        $fields = implode(',', $cols);
        $placeholders = ':email,:hashed'.(isset($params['name']) ? ',:name' : '');
        $stmt = $db->prepare("INSERT INTO users ({$fields}) VALUES ({$placeholders})");
        $stmt->execute($params);
        // Chỉ gán role sau khi user tạo thành công.
        $userId = (int)$db->lastInsertId();
        $roleStmt = $db->prepare('SELECT id FROM roles WHERE name=:name LIMIT 1');
        $roleStmt->execute(['name'=>'admin']);
        $roleId = (int)$roleStmt->fetchColumn();
        if ($roleId < 1) { throw new RuntimeException('Phải chạy RolePermissionSeeder trước.'); }
        $link = $db->prepare('INSERT INTO user_has_roles (user_id, role_id) VALUES (:u,:r)');
        $link->execute(['u'=>$userId, 'r'=>$roleId]);
    }
}
