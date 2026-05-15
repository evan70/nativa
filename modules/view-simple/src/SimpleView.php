<?php

declare(strict_types=1);

namespace App\ViewSimple;

use Marko\Routing\Http\Response;
use Marko\View\TemplateResolverInterface;
use Marko\View\ViewConfig;
use Marko\View\ViewInterface;
use Marko\View\Exceptions\TemplateNotFoundException;

class SimpleView implements ViewInterface
{
    private ?string $layout = null;
    private array $sections = [];
    private ?string $currentSection = null;

    public function __construct(
        private TemplateResolverInterface $resolver,
        private ViewConfig $config,
    ) {}

    public function render(string $template, array $data = []): Response
    {
        return Response::html($this->renderToString($template, $data));
    }

    public function renderToString(string $template, array $data = []): string
    {
        $path = $this->resolver->resolve($template);
        if (!file_exists($path)) {
            throw TemplateNotFoundException::forTemplate($template, [$path]);
        }

        $this->layout = null;
        $this->sections = [];
        $this->currentSection = null;

        extract($data, EXTR_SKIP);

        ob_start();
        try {
            include $path;
            $content = ob_get_clean();
            if ($this->layout !== null) {
                return $this->renderLayout($this->layout);
            }
            return $content;
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
    }

    public function layout(string $layout): void
    {
        $this->layout = $layout;
    }

    public function section(string $name): void
    {
        $this->currentSection = $name;
        ob_start();
    }

    public function endSection(): void
    {
        if ($this->currentSection === null) return;
        $content = ob_get_clean();
        $this->sections[$this->currentSection] = $content;
        $this->currentSection = null;
    }

    public function yield(string $name): string
    {
        return $this->sections[$name] ?? '';
    }

    public function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function renderLayout(string $layout): string
    {
        $layoutPath = $this->resolver->resolve($layout);
        if (!file_exists($layoutPath)) {
            throw TemplateNotFoundException::forTemplate($layout, [$layoutPath]);
        }
        extract($this->sections, EXTR_SKIP);
        ob_start();
        try {
            include $layoutPath;
            return ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
    }
}
