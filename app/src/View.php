<?php

declare(strict_types=1);

namespace App;

use RuntimeException;

final class View
{
    /** @var string[] */
    public static array $pageAssets = [];

    /**
     * @param array<string, mixed> $data
     */
    public static function render(
        string $template,
        array $data = [],
        ?string $layout = 'app/layouts/app',
    ): string {
        $template = str_replace('.', '/', $template);
        if ($layout) {
            $layout = str_replace('.', '/', $layout);
        }

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
        $template = str_replace('.', '/', $template);
        return self::renderFile($template, $data);
    }

    public static function vite(string $entry): string
    {
        if (str_ends_with($entry, '.css')) {
            return '<link rel="stylesheet" href="/mark/' . $entry . '" />';
        }
        return '<script type="module" src="/mark/' . $entry . '"></script>';
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function renderFile(
        string $template,
        array $data = [],
    ): string {
        $viewPath = self::viewsPath() . '/' . ltrim($template, '/') . '.php';

        if (!is_file($viewPath)) {
            throw new RuntimeException("View not found: $viewPath");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewPath;

        return ob_get_clean() ?: '';
    }

    private static function viewsPath(): string
    {
        return dirname(__DIR__, 2) . '/templates';
    }
}
