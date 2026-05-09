<?php

declare(strict_types=1);

use App\Contracts\AssetAwareViewInterface;
use App\ViewAdapter;
use Marko\Authentication\Contracts\UserProviderInterface;
use Marko\Authentication\DefaultUserProvider;
use Marko\Errors\Contracts\ErrorHandlerInterface;
use Marko\ErrorsAdvanced\AdvancedErrorHandler;
use Marko\View\ViewInterface;

return [
    'bindings' => [
        ErrorHandlerInterface::class => AdvancedErrorHandler::class,
        AssetAwareViewInterface::class => function ($container) {
            $phpView = $container->get(Marko\View\PhpView::class);
            return new ViewAdapter($phpView);
        },
        // Also bind ViewInterface to same implementation for code that uses it
        ViewInterface::class => function ($container) {
            return $container->get(AssetAwareViewInterface::class);
        },
    ],
    'preferences' => [
        UserProviderInterface::class => DefaultUserProvider::class,
    ],
];