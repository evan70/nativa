<?php

declare(strict_types=1);

namespace App;

use RuntimeException;

final class View
{
    /** @var string[] */
    public static array $pageAssets = [];

    public static ?string $lcpImage = null;

    private static ?array $manifest = null;

    public static ?string $currentTemplate = null;
    public static ?string $currentPage = null;

    private static function ensureManifestLoaded(): ?array
    {
        if (self::$manifest === null) {
            $path = dirname(__DIR__, 2) . '/public/dist/manifest.json';
            if (is_file($path)) {
                self::$manifest = json_decode(file_get_contents($path), true);
            }
        }
        return self::$manifest;
    }

    private static function findByName(?array $manifest, string $name): ?array
    {
        if (!$manifest) return null;

        foreach ($manifest as $entry) {
            if (isset($entry['name']) && $entry['name'] === $name) {
                return $entry;
            }
        }
        return null;
    }

    public static function render(
        string $template,
        array $data = [],
        ?string $layout = null,
        array $pageAssets = [],
        ?string $lcpImage = null,
    ): string {
        self::$pageAssets = $pageAssets;
        self::$lcpImage = $lcpImage;
        $template = str_replace('.', '/', $template);
        self::$currentTemplate = $template;

        // Auto-detect page type and layout
        $page = PageLayout::detect($template);
        self::$currentPage = $page;

        if ($layout === null) {
            $layout = PageLayout::layoutFile($page);
        }

        if ($layout) {
            $layout = str_replace('.', '/', $layout);
        }

        $content = self::renderFile($template, $data);

        if ($layout === null) {
            return $content;
        }

        return self::renderFile($layout, [...$data, 'content' => $content, 'currentPage' => $page]);
    }

    public static function partial(
        string $template,
        array $data = [],
    ): string {
        $template = str_replace('.', '/', $template);
        return self::renderFile($template, $data);
    }

    public static function resolve(string $path): string
    {
        $manifest = self::ensureManifestLoaded();
        $basePath = '/dist/';

        if ($manifest && isset($manifest[$path]['file'])) {
            return $basePath . $manifest[$path]['file'];
        }

        return $basePath . ltrim($path, '/');
    }

    public static function vite(string $entry, bool $isPageAsset = false): string
    {
        $manifest = self::ensureManifestLoaded();
        $basePath = '/dist/';

        $match = self::findByName($manifest, $entry);

        if (!$match) {
            if (str_ends_with($entry, '.css')) {
                return '<link rel="stylesheet" href="' . $basePath . 'assets/' . $entry . '" />';
            }
            if (str_ends_with($entry, '.js')) {
                return '<script type="module" src="' . $basePath . 'assets/' . $entry . '"></script>';
            }
            return '';
        }

        $html = '';

        // Handle CSS dependencies
        if (isset($match['css'])) {
            foreach ($match['css'] as $css) {
                $html .= '<link rel="stylesheet" href="' . $basePath . $css . '" />' . "\n";
            }
        }

        // Handle main file
        if (isset($match['file'])) {
            $file = $match['file'];
            if (str_ends_with($file, '.css')) {
                $html .= '<link rel="stylesheet" href="' . $basePath . $file . '" />' . "\n";
            } else {
                $html .= '<script type="module" src="' . $basePath . $file . '"></script>' . "\n";
            }
        }

        return trim($html);
    }

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


    /**
     * Get bundled font URL from manifest by partial name match
     */
    public static function fontUrl(string $name): string
    {
        $manifest = self::ensureManifestLoaded();
        if ($manifest) {
            foreach ($manifest as $entry) {
                if (isset($entry['file']) && str_contains($entry['file'], $name)) {
                    return '/dist/' . $entry['file'];
                }
            }
        }
        return '/dist/assets/fonts/' . $name;
    }

}
