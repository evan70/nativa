<?php

declare(strict_types=1);

use App\ViewAdapter;
use Marko\Errors\Contracts\ErrorHandlerInterface;
use Marko\ErrorsAdvanced\AdvancedErrorHandler;
use Marko\View\ViewInterface;

return [
    'bindings' => [
        ErrorHandlerInterface::class => AdvancedErrorHandler::class,
        ViewInterface::class => function ($container) {
            $phpView = $container->get(Marko\View\PhpView::class);
            return new ViewAdapter($phpView);
        },
    ],
];
