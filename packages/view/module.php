<?php

declare(strict_types=1);

use Marko\View\ModuleTemplateResolver;
use Marko\View\TemplateResolverInterface;
use Marko\View\ViewInterface;
use Marko\View\PhpView;

return [
    'bindings' => [
        TemplateResolverInterface::class => ModuleTemplateResolver::class,
        ViewInterface::class => PhpView::class,
    ],
];
