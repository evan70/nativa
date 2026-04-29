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

    /**
     * @param array<string, mixed> $data
     * @param string[] $pageAssets
     */
    public static function render(
        string $template,
        array $data = [],
        ?string $layout = 'app/layouts/app',
        array $pageAssets = [],
        ?string $lcpImage = null,
    ): string {
        self::$pageAssets = $pageAssets;
        self::$lcpImage = $lcpImage;
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

    /**
     * Resolve a path to its hashed version from the manifest
     */
    public static function resolve(string $path): string
    {
        self::ensureManifestLoaded();

        if (isset(self::$manifest[$path]['file'])) {
            return '/cardboard-assets/' . ltrim(self::$manifest[$path]['file'], '/');
        }

        return '/cardboard-assets/' . ltrim($path, '/');
    }

    private static function ensureManifestLoaded(): void
    {
        if (self::$manifest !== null) {
            return;
        }

        $manifestPath = dirname(__DIR__, 2) . '/public/cardboard-assets/vanilla-cards-manifest.json';
        if (is_file($manifestPath)) {
            self::$manifest = json_decode(file_get_contents($manifestPath), true);
        } else {
            self::$manifest = [];
        }
    }

    public static function vite(string $entry, bool $isPageAsset = false): string
    {
        self::ensureManifestLoaded();

        // Try to find the entry in the manifest
        $match = null;
        
        // 1. Exact match by key
        if (isset(self::$manifest[$entry])) {
            $match = self::$manifest[$entry];
        } 
        // 2. Match by name property
        else {
            foreach (self::$manifest as $data) {
                if (isset($data['name']) && $data['name'] === $entry) {
                    $match = $data;
                    break;
                }
            }
        }

        if (!$match) {
            // Fallback for direct files or missed matches
            if (str_ends_with($entry, '.css')) {
                if ($isPageAsset) {
                return '<link rel="preload" href="/cardboard-assets/' . ltrim($entry, '/') . '" as="style" fetchpriority="high" />' . "\n" .
                       '<link rel="stylesheet" href="/cardboard-assets/' . ltrim($entry, '/') . '" fetchpriority="high" />';
                }
                return '<link rel="stylesheet" href="/cardboard-assets/' . ltrim($entry, '/') . '" fetchpriority="high" />';
            }
            if (str_ends_with($entry, '.js') || str_ends_with($entry, '.ts')) {
                return '<script type="module" src="/cardboard-assets/' . ltrim($entry, '/') . '"></script>';
            }
            // If it's just a name without extension, we can't do much without manifest match
            return '';
        }

        $html = '';
        
        // Handle CSS dependencies
        if (isset($match['css'])) {
            foreach ($match['css'] as $css) {
                $cssPath = '/cardboard-assets/' . ltrim($css, '/');
                if ($isPageAsset) {
                    $html .= '<link rel="preload" href="' . $cssPath . '" as="style" fetchpriority="high" />' . "\n";
                    $html .= '<link rel="stylesheet" href="' . $cssPath . '" fetchpriority="high" />' . "\n";
                } else {
                    $html .= '<link rel="stylesheet" href="' . $cssPath . '" fetchpriority="high" />' . "\n";
                }
            }
        }

        // Handle the main file
        if (isset($match['file'])) {
            $file = $match['file'];
            $filePath = '/cardboard-assets/' . ltrim($file, '/');
            if (str_ends_with($file, '.css')) {
                if ($isPageAsset) {
                    $html .= '<link rel="preload" href="' . $filePath . '" as="style" fetchpriority="high" />' . "\n";
                    $html .= '<link rel="stylesheet" href="' . $filePath . '" fetchpriority="high" />' . "\n";
                } else {
                    $html .= '<link rel="stylesheet" href="' . $filePath . '" fetchpriority="high" />' . "\n";
                }
            } else {
                $html .= '<script type="module" src="' . $filePath . '"></script>' . "\n";
            }
        }

        return trim($html);
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
