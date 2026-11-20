<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class View
{
    public static function render(
        string $view,
        array $data = []
    ): string {
        $base = dirname(__DIR__, 2)
            . '/VIew/Pages/';

        $file = $base . $view . '.html';

        $realBase = realpath($base);
        $realFile = realpath($file);

        if (
            $realBase === false ||
            $realFile === false ||
            !str_starts_with(
                $realFile,
                $realBase . DIRECTORY_SEPARATOR
            )
        ) {
            throw new RuntimeException(
                'View file not found.'
            );
        }

        extract($data, EXTR_SKIP);

        ob_start();

        try {
            require $realFile;

            return (string) ob_get_clean();

        } catch (\Throwable $exception) {
            ob_end_clean();

            throw $exception;
        }
    }
}
