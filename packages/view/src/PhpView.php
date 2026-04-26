<?php

declare(strict_types=1);

namespace Marko\View;

use Marko\Routing\Http\Response;
use Throwable;

class PhpView implements ViewInterface
{
    private ?string $layout = null;
    private array $sections = [];
    private array $sectionStack = [];
    private array $dataStack = [];

    public function __construct(
        private TemplateResolverInterface $resolver,
    ) {}

    public function render(
        string $template,
        array $data = [],
    ): Response {
        return Response::html($this->renderToString($template, $data));
    }

    public function renderToString(
        string $template,
        array $data = [],
    ): string {
        $path = $this->resolver->resolve($template);

        $this->layout = null;
        $this->sections = [];
        $this->sectionStack = [];
        $this->dataStack = [];

        $content = $this->renderFile($path, $data);

        if ($this->layout !== null) {
            $layoutPath = $this->resolver->resolve($this->layout);
            if (!isset($this->sections['content'])) {
                $this->sections['content'] = $content;
            }
            return $this->renderFile($layoutPath, $data);
        }

        return $content;
    }

    private function renderFile(string $___path, array $___data): string
    {
        $this->dataStack[] = $___data;
        $___mergedData = array_merge(...$this->dataStack);

        $___output = (function (string $___path, array $___data): string {
            extract($___data, EXTR_SKIP);
            ob_start();

            try {
                include $___path;
            } catch (Throwable $e) {
                ob_end_clean();
                throw $e;
            }

            return ob_get_clean();
        })($___path, $___mergedData);

        array_pop($this->dataStack);

        return $___output;
    }

    /**
     * Set the layout for the current template.
     */
    public function layout(string $name): void
    {
        $this->layout = $name;
    }

    /**
     * Start a section.
     */
    public function section(string $name): void
    {
        $this->sectionStack[] = $name;
        ob_start();
    }

    /**
     * End the current section.
     */
    public function endSection(): void
    {
        $name = array_pop($this->sectionStack);
        if ($name === null) {
            return;
        }
        $this->sections[$name] = ob_get_clean();
    }

    /**
     * Yield the content of a section.
     */
    public function yield(string $name, string $default = ''): string
    {
        return $this->sections[$name] ?? $default;
    }

    /**
     * Include another template.
     */
    public function include(string $template, array $data = []): string
    {
        $path = $this->resolver->resolve($template);
        return $this->renderFile($path, $data);
    }

    /**
     * Escape HTML.
     */
    public function e(mixed $value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}
