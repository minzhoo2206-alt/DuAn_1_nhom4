<?php

declare(strict_types=1);

namespace App\Core;

use FastRoute\RouteCollector;
use FastRoute\Dispatcher;
use function FastRoute\simpleDispatcher;

final class Router
{
    public static function dispatch(Request $request): void
    {
        $response = new Response();

        try {
            $dispatcher = simpleDispatcher(
                function (RouteCollector $routes): void {
                    require dirname(__DIR__, 2)
                        . '/routes/web.php';
                }
            );

            $routeInfo = $dispatcher->dispatch(
                $request->method(),
                $request->path()
            );

            switch ($routeInfo[0]) {

                case Dispatcher::NOT_FOUND:

                    $response->html(
                        '404 - Page Not Found.',
                        404
                    );

                    return;

                case Dispatcher::METHOD_NOT_ALLOWED:

                    $response->html(
                        '405 - Method Not Allowed.',
                        405
                    );

                    return;

                case Dispatcher::FOUND:

                    $route = $routeInfo[1];
                    $params = $routeInfo[2];

                    $handler = $route['handler'];
                    $middlewares = $route['middleware'] ?? [];

                    $coreMiddlewares = [];

                    foreach ($middlewares as $middlewareClass) {
                        $coreMiddlewares[] = new $middlewareClass();
                    }

                    $next = function (
                        Request $request,
                        Response $response
                    ) use ($handler, $params): void {

                        [$controllerClass, $method] = $handler;

                        $controller = new $controllerClass();

                        $controller->{$method}(
                            $request,
                            $response,
                            $params
                        );
                    };

                    foreach (
                        array_reverse($coreMiddlewares)
                        as $middleware
                    ) {
                        $next = function (
                            Request $request,
                            Response $response
                        ) use ($middleware, $next): void {

                            $middleware->handle(
                                $request,
                                $response,
                                $next
                            );
                        };
                    }

                    $next($request, $response);

                    return;
            }

        } catch (HttpException $exception) {

            $response->html(
                htmlspecialchars(
                    $exception->getMessage(),
                    ENT_QUOTES,
                    'UTF-8'
                ),
                $exception->statusCode()
            );

        } catch (\Throwable $exception) {

            error_log(
                '[APPLICATION ERROR] ' .
                $exception->getMessage()
            );

            $response->html(
                '500 - Internal Server Error.',
                500
            );
        }
    }
}
