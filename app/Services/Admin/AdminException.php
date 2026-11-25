<?php
declare(strict_types=1);
namespace App\Services\Admin;

use RuntimeException;

final class AdminException extends RuntimeException
{
    public function __construct(string $message, private int $httpStatus = 400)
    {
        parent::__construct($message);
    }

    public function status(): int { return $this->httpStatus; }
}
