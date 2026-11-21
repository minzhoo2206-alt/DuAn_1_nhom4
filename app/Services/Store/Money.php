<?php
declare(strict_types=1);
namespace App\Services\Store;

use DomainException;

/** Tiền dùng số nguyên cent để tránh lỗi cộng float (DECIMAL(15,2) trong SQL). */
final class Money
{
    public static function cents(int|float|string $decimal): int
    {
        $text = (string) $decimal;
        if (!preg_match('/^\d{1,13}(?:\.\d{1,2})?$/D', $text)) {
            throw new DomainException('Giá tiền trong database không hợp lệ.');
        }
        [$whole, $fraction] = array_pad(explode('.', $text, 2), 2, '');
        return (int) $whole * 100 + (int) str_pad($fraction, 2, '0');
    }

    public static function decimal(int $cents): string
    {
        if ($cents < 0) throw new DomainException('Số tiền không được âm.');
        return intdiv($cents, 100) . '.' . str_pad((string)($cents % 100), 2, '0', STR_PAD_LEFT);
    }

    public static function format(int $cents): string
    {
        return number_format($cents / 100, 0, ',', '.') . ' đ';
    }

    /** Giá niêm yết là giá thanh toán (đã gồm VAT nếu áp dụng), không cộng VAT lần 2. */
    public static function includedTaxCents(int $grossCents, string|int|float $vatRate, bool $applicable): int
    {
        if (!$applicable) return 0;
        $rate = (float) $vatRate;
        if ($rate < 0 || $rate > 100) throw new DomainException('Thuế VAT không hợp lệ.');
        return (int) round($grossCents * $rate / (100 + $rate), 0, PHP_ROUND_HALF_UP);
    }
}
