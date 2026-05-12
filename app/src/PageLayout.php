<?php

declare(strict_types=1);

namespace App;

final class PageLayout
{
    /**
     * Maps template path segments to page names.
     * Key = folder name under templates/pages/ (or templates/ for partials/layouts)
     * Value = page identifier used for Vite entry (page-<name>) and body class
     */
    private const PAGE_MAP = [
        // New flat structure (templates/pages/<name>/)
        'home'       => 'home',
        'portfolio'  => 'portfolio',
        'articles'   => 'articles',
        'dash'       => 'dash',
        'auth'       => 'auth',
        'errors'     => 'errors',
        'fire-show-demo' => 'fire-show-demo',

        // Backward compatibility (old template paths)
        'blog'       => 'articles',
        'cardboard'  => 'dash',
        'admin'      => 'dash',
    ];

    private const LAYOUT_MAP = [
        'dash'   => 'layouts/admin',
        'auth'   => 'layouts/auth',
    ];

    private const BODY_CLASS_MAP = [
        'dash'   => 'layout-admin',
        'errors' => 'page-errors',
    ];

    /**
     * Detect page name from template path.
     *
     * Template paths use dot notation (e.g. "pages/portfolio/show")
     * or slash notation. We check each segment against PAGE_MAP.
     *
     * Specific segments (auth, errors) take priority over generic
     * container segments (cardboard, app, blog) when both match.
     */
    public static function detect(string $template): string
    {
        $template = str_replace(['.', '::'], '/', $template);
        $parts = explode('/', $template);

        // Container segments are generic — skip if a more specific match exists
        $containerSegments = ['app', 'cardboard', 'blog', 'pages'];

        $containerMatch = null;
        foreach ($parts as $part) {
            if (!isset(self::PAGE_MAP[$part])) {
                continue;
            }
            // Specific match (not a container) — return immediately
            if (!in_array($part, $containerSegments, true)) {
                return self::PAGE_MAP[$part];
            }
            // Container match — remember but keep looking
            if ($containerMatch === null) {
                $containerMatch = self::PAGE_MAP[$part];
            }
        }

        return $containerMatch ?? 'home';
    }

    /**
     * Get the body CSS class for a page.
     */
    public static function bodyClass(string $page): string
    {
        return self::BODY_CLASS_MAP[$page] ?? 'page-' . $page;
    }

    /**
     * Get the layout file path for a page.
     */
    public static function layoutFile(string $page): string
    {
        return self::LAYOUT_MAP[$page] ?? 'layouts/app';
    }
}
