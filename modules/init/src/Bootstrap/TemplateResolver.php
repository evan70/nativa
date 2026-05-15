<?php

declare(strict_types=1);

namespace App\Init\Bootstrap;

use Marko\View\Exceptions\TemplateNotFoundException;
use Marko\View\TemplateResolverInterface;

/**
 * Custom template resolver that first checks the upstream ModuleTemplateResolver,
 * then falls back to the project's shared templates/ directory.
 *
 * Module-scoped templates (blog::post/show) are resolved by the upstream resolver.
 * Unprefixed templates fall through to templates/ as a second pass, allowing
 * project-wide template overrides. Dot notation (layouts.app) is converted to
 * directory separators (layouts/app.php).
 */
final class TemplateResolver implements TemplateResolverInterface
{
    private string $templatesDir;

    public function __construct(
        private TemplateResolverInterface $upstream,
        string $basePath,
    ) {
        $this->templatesDir = $basePath . '/templates';
    }

    public function resolve(string $template): string
    {
        // 1. Try upstream resolver first (modules resources/views/)
        try {
            return $this->upstream->resolve($template);

        } catch (TemplateNotFoundException $e) {
            // 2. Fall back to templates/ directory
            $searched = $this->upstream->getSearchedPaths($template);
            $path = $this->templatePath($template);

            if (file_exists($path)) {
                return $path;
            }

            $searched[] = $path;

            throw TemplateNotFoundException::forTemplate($template, $searched);
        }
    }

    public function getSearchedPaths(string $template): array
    {
        $paths = $this->upstream->getSearchedPaths($template);
        $paths[] = $this->templatePath($template);

        return $paths;
    }

    /**
     * Convert template name to filesystem path.
     * Converts dot notation (layouts.app) to directory separators (layouts/app.php).
     */
    private function templatePath(string $template): string
    {
        $path = str_replace('.', '/', $template);

        return $this->templatesDir . '/' . $path . '.php';
    }
}
