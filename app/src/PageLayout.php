<?php

declare(strict_types=1);

namespace App;

final class PageLayout
{
    private const PAGE_MAP = [
        // Dashboard pages
        'cardboard' => 'dash',
        'admin'     => 'dash',

        // Auth pages
        'login'     => 'auth',
        'register'  => 'auth',
        'password'  => 'auth',

        // Front pages (default)
        'home'      => 'home',
        'blog'      => 'home',
        'about'     => 'home',
    ];

    public static function detect(string $template): string
    {
        $template = str_replace('.', '/', $template);
        $parts = explode('/', $template);
        $first = $parts[0] ?? '';

        return self::PAGE_MAP[$first] ?? 'home';
    }

    public static function bodyClass(string $page): string
    {
        return match ($page) {
            'dash'  => 'admin-layout',
            'auth'  => 'auth-page',
            default => 'front-page',
        };
    }

    public static function layoutFile(string $page): ?string
    {
        return match ($page) {
            'dash'  => 'cardboard/layout/base',
            'auth'  => 'cardboard/auth/base',
            default => 'app/layouts/app',
        };
    }
}
