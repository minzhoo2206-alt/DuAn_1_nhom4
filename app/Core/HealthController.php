<?php

declare(strict_types=1);

namespace App\Core;

final class HealthController
{
    public function index(
        Request $request,
        Response $response
    ): void {
        $response->json([
            'success' => true,
            'message' => 'Phone Store Core is running',
            'method' => $request->method(),
            'path' => $request->path()
        ]);
    }
}
