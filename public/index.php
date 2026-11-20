<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$requestPath = is_string($requestPath) ? rawurldecode($requestPath) : '/';

// Keep the current project layout: public HTML pages live in VIew/Pages,
// admin pages live in admin, and static assets live in assets.
$staticFile = null;

if ($requestPath === '/' || preg_match('~^/[a-z0-9-]+\.html$~i', $requestPath)) {
    $page = $requestPath === '/' ? 'index.html' : basename($requestPath);
    $staticFile = $root . '/VIew/Pages/' . $page;
} elseif (preg_match('~^/(admin|assets)/[a-zA-Z0-9_./-]+$~', $requestPath)) {
    $relativePath = ltrim($requestPath, '/');
    $segments = explode('/', $relativePath);

    if (!in_array('..', $segments, true)) {
        $staticFile = $root . '/' . $relativePath;
    }
}

if ($staticFile !== null) {
    $realRoot = realpath($root);
    $realFile = realpath($staticFile);

    if (
        $realRoot !== false &&
        $realFile !== false &&
        str_starts_with($realFile, $realRoot . DIRECTORY_SEPARATOR) &&
        is_file($realFile)
    ) {
        $mimeTypes = [
            'css' => 'text/css; charset=utf-8',
            'html' => 'text/html; charset=utf-8',
            'js' => 'application/javascript; charset=utf-8',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
        ];
        $extension = strtolower(pathinfo($realFile, PATHINFO_EXTENSION));

        if (isset($mimeTypes[$extension])) {
            header('Content-Type: ' . $mimeTypes[$extension]);
            readfile($realFile);
            exit;
        }
    }
}

require $root . '/bootstrap/app.php';
