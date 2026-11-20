<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    public function html(
        string $content,
        int $status = 200
    ): void {
        http_response_code($status);

        header('Content-Type: text/html; charset=utf-8');

        echo $content;
    }

    public function json(
        array $data,
        int $status = 200
    ): void {
        http_response_code($status);

        header('Content-Type: application/json; charset=utf-8');

        echo json_encode(
            $data,
            JSON_UNESCAPED_UNICODE |
            JSON_THROW_ON_ERROR
        );
    }

    public function redirect(
        string $url,
        int $status = 302
    ): never {
        header('Location: ' . $url, true, $status);

        exit;
    }

    public function status(int $status): void
    {
        http_response_code($status);
    }

    public function noContent(): void
    {
        http_response_code(204);
    }
}
