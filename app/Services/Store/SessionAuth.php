<?php
declare(strict_types=1);
namespace App\Services\Store;

final class SessionAuth
{
    public static function id(): ?int
    {
        $id = $_SESSION['user_id'] ?? null;
        return is_int($id) && $id > 0 ? $id : null;
    }
    public static function login(int $id): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $id;
        CsrfService::rotate();
    }
    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 3600,
                'path' => $p['path'],
                'domain' => $p['domain'],
                'secure' => (bool)$p['secure'],
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
        session_destroy();
    }
}
