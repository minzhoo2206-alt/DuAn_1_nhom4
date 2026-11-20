<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\HttpException;

final class CsrfMiddleware implements MiddlewareInterface
{
    public function handle(
        Request $request,
        Response $response,
        callable $next
    ): void {
        $method = $request->method();

        $protectedMethods = [
            'POST',
            'PUT',
            'PATCH',
            'DELETE'
        ];

        if (in_array($method, $protectedMethods, true)) {

            $token = $request->input('_token');

            $sessionToken = $_SESSION['_csrf_token'] ?? '';

            if (
                !is_string($token) ||
                !is_string($sessionToken) ||
                $sessionToken === '' ||
                !hash_equals($sessionToken, $token)
            ) {
                throw new HttpException(
                    'CSRF token không hợp lệ.',
                    419
                );
            }
        }

        $next($request, $response);
    }
}
