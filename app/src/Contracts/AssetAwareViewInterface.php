<?php

declare(strict_types=1);

namespace App\Contracts;

use Marko\View\ViewInterface;

/**
 * Extended view interface with asset management support.
 */
interface AssetAwareViewInterface extends ViewInterface
{
    /**
     * Set LCP (Largest Contentful Paint) image URL for preload.
     */
    public function withLcpImage(string $url): self;

    /**
     * Set assets for the page.
     *
     * @param string $page Page identifier (e.g., 'app/home')
     * @param array<int, string> $js JS asset handles to include
     * @param array<int, string> $css CSS asset handles to include
     * @return self Fluent interface
     */
    public function withAssets(string $page, array $js, array $css): self;
}