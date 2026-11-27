<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    private ?array $jsonData = null;

    public function method(): string
    {
        return strtoupper(
            $_SERVER['REQUEST_METHOD'] ?? 'GET'
        );
    }

    public function path(): string
    {
        $path = parse_url(
            $_SERVER['REQUEST_URI'] ?? '/',
            PHP_URL_PATH
        );

        return '/' . trim($path ?: '/', '/');
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        $data = $this->all();

        return $data[$key] ?? $default;
    }

    public function all(): array
    {
        $method = $this->method();

        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $contentType = strtolower($_SERVER['CONTENT_TYPE'] ?? '');

            if (str_contains($contentType, 'application/json')) {
                return $this->json();
            }

            if ($method === 'POST') {
                return $_POST;
            }

            $content = file_get_contents('php://input');
            $formData = [];

            if ($content !== false && $content !== '') {
                parse_str($content, $formData);
            }

            return $formData;
        }

        return $_GET;
    }

    public function json(): array
    {
        if ($this->jsonData !== null) {
            return $this->jsonData;
        }

        $content = file_get_contents('php://input');

        if ($content === false || trim($content) === '') {
            return $this->jsonData = [];
        }

        $data = json_decode($content, true);

        if (!is_array($data)) {
            throw new HttpException(
                'Invalid JSON payload.',
                400
            );
        }

        return $this->jsonData = $data;
    }

    public function header(string $name): ?string
    {
        $normalizedName = strtoupper(str_replace('-', '_', $name));
        $key = in_array(
            $normalizedName,
            ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'],
            true
        ) ? $normalizedName : 'HTTP_' . $normalizedName;

        return $_SERVER[$key] ?? null;
    }
}
