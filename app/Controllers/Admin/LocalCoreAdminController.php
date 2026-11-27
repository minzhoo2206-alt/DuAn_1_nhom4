<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;

/** Transitional LOCAL adapter. No production deployment. */
final class LocalCoreAdminController
{
    public function dispatch(Request $request, Response $response, array $params): void
    {
        if (PHP_SAPI !== 'cli-server'
            || !in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)
            || ($_SERVER['HTTP_HOST'] ?? '') !== '127.0.0.1:8002'
            || session_status() !== PHP_SESSION_ACTIVE
            || session_name() !== 'FOURTECH_ADMIN_INTEGRATION') {
            $response->html('Local integration only.', 403);
            return;
        }
        // Keep existing RBAC, authentication, and per-action CSRF checks intact.
        define('FOURTECH_ADMIN_CORE_LOCAL', true);
        require dirname(__DIR__, 3) . '/docs/admin-db-test.php';
    }
}
