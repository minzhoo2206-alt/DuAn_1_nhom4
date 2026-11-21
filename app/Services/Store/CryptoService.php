<?php
declare(strict_types=1);
namespace App\Services\Store;

use RuntimeException;

/** Mã hóa PII đúng tên *_ciphertext của ERD, AES-256-GCM có xác thực. */
final class CryptoService
{
    private string $key;
    public function __construct()
    {
        $encoded = $_ENV['APP_KEY'] ?? getenv('APP_KEY') ?: '';
        $key = base64_decode((string)$encoded, true);
        if ($key === false || strlen($key) !== 32) {
            throw new RuntimeException('Thiếu APP_KEY. Tạo khóa base64 32 byte theo README; không commit khóa.');
        }
        $this->key = $key;
    }

    public function encrypt(string $text): string
    {
        $nonce = random_bytes(12);
        $ciphertext = openssl_encrypt($text, 'aes-256-gcm', $this->key, OPENSSL_RAW_DATA, $nonce, $tag);
        if ($ciphertext === false || strlen($tag) !== 16) {
            throw new RuntimeException('Không thể mã hóa dữ liệu khách hàng.');
        }
        return 'v1:' . base64_encode($nonce . $tag . $ciphertext);
    }

    public function decrypt(?string $stored): string
    {
        if ($stored === null || $stored === '') return '';
        if (!str_starts_with($stored, 'v1:')) throw new RuntimeException('Định dạng dữ liệu mã hóa không hợp lệ.');
        $data = base64_decode(substr($stored, 3), true);
        if ($data === false || strlen($data) < 28) throw new RuntimeException('Dữ liệu mã hóa bị lỗi.');
        $plaintext = openssl_decrypt(substr($data, 28), 'aes-256-gcm', $this->key,
            OPENSSL_RAW_DATA, substr($data, 0, 12), substr($data, 12, 16));
        if ($plaintext === false) throw new RuntimeException('Không giải mã được dữ liệu: kiểm tra APP_KEY.');
        return $plaintext;
    }
}
