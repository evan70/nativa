<?php

declare(strict_types=1);

namespace App;

use RuntimeException;

final class View
{
    /** @var string[] */
    public static array $pageAssets = [];

    public static ?string $lcpImage = null;

    /** @var array<string, array{name: string, file: string, css?: array<int, string>, assets?: array<int, string>}>|null */
    private static ?array $manifest = null;

    public static ?string $currentTemplate = null;
    public static ?string $currentPage = null;

    /**
     * @return array<string, array{name: string, file: string, css?: array<int, string>, assets?: array<int, string>}>|null
     */
    private static function ensureManifestLoaded(): ?array
    {
        if (self::$manifest === null) {
            $path = dirname(__DIR__, 3) . '/public/dist/manifest.json';
            $content = is_file($path) ? file_get_contents($path) : null;
            if (is_string($content)) {
                /** @var mixed $decoded */
                $decoded = json_decode($content, true);
                /** @var array<string, array{name: string, file: string, css?: array<int, string>, assets?: array<int, string>}>|false $parsed */
                $parsed = is_array($decoded) ? $decoded : false;
                if ($parsed !== false) {
                    self::$manifest = $parsed;
                }
            }
        }
        return self::$manifest;
    }

    /**
     * @param array<string, array{name: string, file: string, css?: array<int, string>, assets?: array<int, string>}>|null $manifest
     * @return array{name: string, file: string, css?: array<int, string>, assets?: array<int, string>}|null
     */
    private static function findByName(?array $manifest, string $name): ?array
    {
        if (!$manifest) return null;

        foreach ($manifest as $entry) {
            if ($entry['name'] === $name) {
                /** @var array{name: string, file: string, css?: array<int, string>, assets?: array<int, string>} $entry */
                return $entry;
            }
        }
        
        // Robust fallback for keys
        foreach ($manifest as $srcPath => $entry) {
            if ($srcPath === $name || 
                basename($srcPath) === $name || 
                basename($srcPath) === $name . '.ts' || 
                basename($srcPath) === $name . '.js' || 
                basename($srcPath) === $name . '.css') {
                 return $entry;
            }
        }
        
        return null;
    }

    /**
     * @param array<string, mixed> $data
     * @param string[] $pageAssets
     */
    public static function render(
        string $template,
        array $data = [],
        ?string $layout = null,
        array $pageAssets = [],
        ?string $lcpImage = null,
    ): string {
        self::$pageAssets = $pageAssets;
        
        // Only override LCP if explicitly passed (don't reset controller's value)
        if ($lcpImage !== null) {
            self::$lcpImage = $lcpImage;
        }
        $template = str_replace('.', '/', $template);
        self::$currentTemplate = $template;

        // Auto-detect page type and layout
        $page = PageLayout::detect($template);
        self::$currentPage = $page;

        // Use caller's layout, or default from PageLayout
        // Empty string '' means "no layout"
        $layoutFile = match (true) {
            $layout === '' => null,
            $layout !== null => $layout,
            default => PageLayout::layoutFile($page),
        };

        if ($layoutFile) {
            $layoutFile = str_replace('.', '/', $layoutFile);
        }

        $content = self::renderFile($template, $data);

        if ($layoutFile === null) {
            return $content;
        }

        return self::renderFile($layoutFile, [...$data, 'content' => $content, 'currentPage' => $page]);
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
        return self::viteCss($entry) . self::viteJs($entry);
    }

    public static function viteCss(string $entry): string
    {
        $manifest = self::ensureManifestLoaded();
        $basePath = '/dist/';
        $match = self::findByName($manifest, $entry);
        $html = '';

        if ($match !== null && isset($match['css'])) {
            foreach ($match['css'] as $css) {
                $url = $basePath . $css;
                $html .= '<link rel="stylesheet" href="' . $url . '" fetchpriority="high">' . "\n";
            }
        }

        if (!$match && str_ends_with($entry, '.css')) {
            $url = $basePath . 'assets/' . $entry;
            $html .= '<link rel="stylesheet" href="' . $url . '" fetchpriority="high">' . "\n";
        }

        return $html;
    }

    public static function viteJs(string $entry, bool $defer = false): string
    {
        $manifest = self::ensureManifestLoaded();
        $basePath = '/dist/';
        $match = self::findByName($manifest, $entry);
        $html = '';
        
        // Determine defer attribute
        // Note: type="module" is deferred by default.
        // We only add 'defer' attribute if explicitly requested.
        $deferAttr = $defer ? ' defer' : '';
        $crossorigin = $entry === 'init' ? ' crossorigin="anonymous"' : '';

        if ($match !== null) {
            $file = $match['file'];
            if (str_ends_with($file, '.js')) {
                // Do not preload scripts that are already included in the head to avoid double download warnings in some browsers
                // especially when they are deferred.
                $html .= '<script type="module" src="' . $basePath . $file . '"' . $deferAttr . $crossorigin . '></script>' . "\n";
            }
        }

        if (!$match && str_ends_with($entry, '.js')) {
            $url = $basePath . 'assets/' . $entry;
            $html .= '<script type="module" src="' . $url . '"' . $deferAttr . '></script>' . "\n";
        }

        return $html;
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
        return dirname(__DIR__, 3) . '/templates';
    }


    /**
     * Get bundled font URL from manifest by partial name match
     */
    public static function fontUrl(string $name): string
    {
        $manifest = self::ensureManifestLoaded();
        if ($manifest) {
            foreach ($manifest as $entry) {
                if (str_contains($entry['file'], $name)) {
                    return '/dist/' . $entry['file'];
                }
            }
        }
        return '/dist/assets/fonts/' . $name;
    }

    /**
     * Generate <link rel="preload"> for fonts used on this page
     * These fonts are in core bundle and needed early to prevent FOUT
     */
    public static function fontPreloads(): string
    {
        $manifest = self::ensureManifestLoaded();
        if (!$manifest) {
            return '';
        }

        $html = '';

        // Find font assets from core bundle (which is always loaded)
        foreach ($manifest as $entry) {
            if (isset($entry['assets'])) {
                foreach ($entry['assets'] as $asset) {
                    if (preg_match('/\.(woff2?|ttf|otf)$/', $asset)) {
                        $type = str_ends_with($asset, '.woff2') ? 'font/woff2' : 'font/ttf';
                        $html .= '<link rel="preload" href="/dist/' . $asset . '" as="font" type="' . $type . '" crossorigin>' . "\n";
                    }
                }
            }
        }

        return $html;
    }
}
