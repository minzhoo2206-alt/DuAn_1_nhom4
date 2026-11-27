<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\HttpException;

final class AuthMiddleware implements MiddlewareInterface
{
    public function handle(
        Request $request,
        Response $response,
        callable $next
    ): void {
        if (empty($_SESSION['user'])) {
            throw new HttpException(
                'Bạn cần đăng nhập để tiếp tục.',
                401
            );
        }

        $next($request, $response);
    }
}
