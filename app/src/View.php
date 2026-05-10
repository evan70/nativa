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

        if ($match && isset($match['css'])) {
            foreach ($match['css'] as $css) {
                $url = $basePath . $css;
                $html .= '<link rel="preload" href="' . $url . '" as="style">' . "\n";
                $html .= '<link rel="stylesheet" href="' . $url . '" fetchpriority="high">' . "\n";
            }
        }

        if (!$match && str_ends_with($entry, '.css')) {
            $url = $basePath . 'assets/' . $entry;
            $html .= '<link rel="preload" href="' . $url . '" as="style">' . "\n";
            $html .= '<link rel="stylesheet" href="' . $url . '" fetchpriority="high">';
        }

        return $html;
    }

    public static function viteJs(string $entry, bool $defer = false): string
    {
        $manifest = self::ensureManifestLoaded();
        $basePath = '/dist/';
        $match = self::findByName($manifest, $entry);
        $html = '';
        
        // Determine defer attribute - init.js always needs defer for FOUC prevention
        $isInit = $entry === 'init';
        $shouldDefer = $defer || $isInit;
        $deferAttr = $shouldDefer ? ' defer' : '';
        $crossorigin = $isInit ? ' crossorigin="anonymous"' : '';

        if ($match && isset($match['file'])) {
            $file = $match['file'];
            if (str_ends_with($file, '.js')) {
                if (!$shouldDefer) {
                    $html .= '<link rel="modulepreload" href="' . $basePath . $file . '">' . "\n";
                }
                $html .= '<script type="module" src="' . $basePath . $file . '"' . $deferAttr . $crossorigin . '></script>' . "\n";
            }
        }

        if (!$match && str_ends_with($entry, '.js')) {
            $url = $basePath . 'assets/' . $entry;
            if (!$shouldDefer) {
                $html .= '<link rel="modulepreload" href="' . $url . '">' . "\n";
            }
            $html .= '<script type="module" src="' . $url . '"' . $deferAttr . '></script>';
        }

        return $html;
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
