<?php

declare(strict_types=1);

namespace App;

use RuntimeException;

final class View
{
    /** @var string[] */
    public static array $pageAssets = [];

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
    ): string {
        self::$pageAssets = $pageAssets;
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
        if (self::$manifest === null) {
            $manifestPath = dirname(__DIR__, 2) . '/public/mark/vanilla-cards-manifest.json';
            if (is_file($manifestPath)) {
                self::$manifest = json_decode(file_get_contents($manifestPath), true);
            } else {
                self::$manifest = [];
            }
        }

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
                return '<link rel="stylesheet" href="/mark/' . ltrim($entry, '/') . '" />';
            }
            if (str_ends_with($entry, '.js') || str_ends_with($entry, '.ts')) {
                return '<script type="module" src="/mark/' . ltrim($entry, '/') . '"></script>';
            }
            // If it's just a name without extension, we can't do much without manifest match
            return '';
        }

        $html = '';
        
        // Handle CSS dependencies
        if (isset($match['css'])) {
            foreach ($match['css'] as $css) {
                $html .= '<link rel="stylesheet" href="/mark/' . ltrim($css, '/') . '" />' . "\n";
            }
        }

        // Handle the main file
        if (isset($match['file'])) {
            $file = $match['file'];
            if (str_ends_with($file, '.css')) {
                $html .= '<link rel="stylesheet" href="/mark/' . ltrim($file, '/') . '" />' . "\n";
            } else {
                $html .= '<script type="module" src="/mark/' . ltrim($file, '/') . '"></script>' . "\n";
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
