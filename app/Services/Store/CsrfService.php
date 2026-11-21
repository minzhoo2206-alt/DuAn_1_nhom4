<?php
declare(strict_types=1);
namespace App\Services\Store;

final class CsrfService
{
    public static function token(): string
    {
        if (!isset($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return (string) $_SESSION['csrf_token'];
    }
    public static function verify(mixed $token): bool
    {
        return is_string($token) && hash_equals(self::token(), $token);
    }
    public static function rotate(): void
    {
        unset($_SESSION['csrf_token']);
    }
}
