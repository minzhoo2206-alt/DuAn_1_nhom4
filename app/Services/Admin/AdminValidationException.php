<?php
declare(strict_types=1);
namespace App\Services\Admin;

use InvalidArgumentException;

final class AdminValidationException extends InvalidArgumentException
{
    public function __construct(private array $errors)
    {
        parent::__construct('Dữ liệu chưa hợp lệ. Vui lòng kiểm tra các trường.');
    }

    public function errors(): array { return $this->errors; }
}
