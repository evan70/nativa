<?php

declare(strict_types=1);

namespace App;

use Marko\Routing\Http\Response;
use Marko\View\ViewInterface;

class ViewAdapter implements ViewInterface
{
    private array $customAssets = [];

    public function __construct(
        private readonly ViewInterface $view,
    ) {}

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
        
        // Detect assets for the page
        $js = [$templatePath];
        $css = [];

        if (isset($this->customAssets[$templatePath])) {
            $js = array_merge($js, $this->customAssets[$templatePath]['js']);
            $css = array_merge($css, $this->customAssets[$templatePath]['css']);
        }

        // Set the assets in View so the layout can access them
        View::$pageAssets = array_merge($js, $css);

        return $this->view->renderToString($templatePath, $data);
    }
}
