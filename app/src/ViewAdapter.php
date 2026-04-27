<?php

declare(strict_types=1);

namespace App;

use Marko\Routing\Http\Response;
use Marko\View\ViewInterface;

class ViewAdapter implements ViewInterface
{
    public function __construct(
        private readonly ViewInterface $view,
    ) {}

    public function render(string $template, array $data = []): Response
    {
        return Response::html($this->renderToString($template, $data));
    }

    public function renderToString(string $template, array $data = []): string
    {
        $templatePath = str_replace('.', '/', $template);
        
        // Clear previous assets and register new ones
        View::$pageAssets = [
            "{$templatePath}.js",
            "{$templatePath}.css",
        ];

        return $this->view->renderToString($templatePath, $data);
    }
}
