<?php

declare(strict_types=1);

namespace App\Core\Middleware;

use App\Core\Request;
use App\Core\Response;

final class GuestMiddleware implements MiddlewareInterface
{
    public function handle(
        Request $request,
        Response $response,
        callable $next
    ): void {
        if (!empty($_SESSION['user'])) {
            $response->redirect('/');

            return;
        }

        $next($request, $response);
    }
}
