<?php

declare(strict_types=1);

namespace App;

use Marko\View\ModuleTemplateResolver;
use Marko\View\TemplateResolverInterface;
use Marko\View\Exceptions\TemplateNotFoundException;

class AppTemplateResolver implements TemplateResolverInterface
{
    private ModuleTemplateResolver $moduleResolver;

    public function __construct(
        ModuleTemplateResolver $moduleResolver,
    ) {
        $this->moduleResolver = $moduleResolver;
    }

    public function resolve(string $template): string
    {
        // First check app's templates folder
        $appTemplatesPath = dirname(__DIR__, 2) . '/templates';
        $extension = '.php';
        
        // Parse template to get relative path
        $relativePath = str_replace(['.', '::'], '/', $template);
        $fullPath = $appTemplatesPath . '/' . $relativePath . $extension;
        
        if (file_exists($fullPath)) {
            return $fullPath;
        }
        
        // Fall back to module resolver
        return $this->moduleResolver->resolve($template);
    }

    public function getSearchedPaths(string $template): array
    {
        // Return searched paths from both resolvers
        $appTemplatesPath = dirname(__DIR__, 2) . '/templates';
        $relativePath = str_replace(['.', '::'], '/', $template);
        $extension = '.php';
        
        return [
            $appTemplatesPath . '/' . $relativePath . $extension,
            ...$this->moduleResolver->getSearchedPaths($template),
        ];
    }
}