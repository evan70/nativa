<?php

declare(strict_types=1);

namespace App;

use RuntimeException;

final class View
{
    /**
     * @param array<string, mixed> $data
     */
    public static function render(
        string $template,
        array $data = [],
        ?string $layout = 'layouts/app.phtml',
    ): string {
        $content = self::renderFile($template, $data);

        if ($layout === null) {
            return $content;
        }

        return self::renderFile($layout, [...$data, 'content' => $content]);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function partial(
        string $template,
        array $data = [],
    ): string {
        return self::renderFile($template, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function renderFile(
        string $template,
        array $data = [],
    ): string {
        $viewPath = self::viewsPath() . '/' . ltrim($template, '/');

        if (!is_file($viewPath)) {
            throw new RuntimeException("View not found: $viewPath");
        }

        $include = static fn (string $partial, array $partialData = []): string => self::partial($partial, $partialData);
        extract($data, EXTR_SKIP);

        ob_start();
        require $viewPath;

        return ob_get_clean() ?: '';
    }

    private static function viewsPath(): string
    {
        return dirname(__DIR__, 2) . '/views';
    }
}
