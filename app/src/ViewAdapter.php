<?php

declare(strict_types=1);

namespace App;

use App\Contracts\AssetAwareViewInterface;
use Marko\Routing\Http\Response;
use Marko\View\ViewInterface;

class ViewAdapter implements ViewInterface, AssetAwareViewInterface
{
    private array $customAssets = [];
    private ?string $lcpImage = null;

    public function __construct(
        private readonly ViewInterface $view,
    ) {}

    public function withLcpImage(string $url): self
    {
        $this->lcpImage = $url;
        return $this;
    }

    public function withAssets(string $page, array $js, array $css): self
    {
        $page = str_replace('.', '/', $page);
        $this->customAssets[$page] = [
            'js' => $js,
            'css' => $css,
        ];
        return $this;
    }

    public function render(string $template, array $data = []): Response
    {
        return Response::html($this->renderToString($template, $data));
    }

    public function renderToString(string $template, array $data = []): string
    {
        $templatePath = str_replace('.', '/', $template);

        // Set current template and page for asset resolution
        View::$currentTemplate = $templatePath;
        View::$currentPage = PageLayout::detect($templatePath);

        // Detect assets for the page
        $js = [$templatePath];
        $css = [];

        if (isset($this->customAssets[$templatePath])) {
            $js = array_merge($js, $this->customAssets[$templatePath]['js']);
            $css = array_merge($css, $this->customAssets[$templatePath]['css']);
        }

        View::$pageAssets = array_merge($js, $css);
        
        // Only override LCP if adapter explicitly set it (don't reset controller's value)
        if ($this->lcpImage !== null) {
            View::$lcpImage = $this->lcpImage;
        }

        // Pass current page and template to the view so layouts can access them
        $data['currentPage'] = View::$currentPage;
        $data['currentTemplate'] = View::$currentTemplate;

        return $this->view->renderToString($templatePath, $data);
    }
}