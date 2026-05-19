<?php

declare(strict_types=1);

use App\AppTemplateResolver;
use App\Contracts\AssetAwareViewInterface;
use App\ViewAdapter;
use App\ViewSimple\SimpleView;
use Marko\Authentication\Contracts\UserProviderInterface;
use Marko\Authentication\DefaultUserProvider;
use Marko\Core\Container\ContainerInterface;
use Marko\Errors\Contracts\ErrorHandlerInterface;
use Marko\ErrorsAdvanced\AdvancedErrorHandler;
use Marko\View\TemplateResolverInterface;
use Marko\View\ViewInterface;

return [
    'bindings' => [
        // Error handling is managed by marko/errors-simple (core)
        
        AssetAwareViewInterface::class => function (ContainerInterface $container): AssetAwareViewInterface {
            /** @var \Marko\View\TemplateResolverInterface $resolver */
            $resolver = $container->get(\Marko\View\TemplateResolverInterface::class);
            /** @var \Marko\View\ViewConfig $config */
            $config = $container->get(\Marko\View\ViewConfig::class);

            $view = new SimpleView($resolver, $config);

            return new ViewAdapter($view);
        },
    ],
    'sequence' => [
        'after' => ['app/view-simple', 'app/init'],
    ],
    'preferences' => [
        UserProviderInterface::class => DefaultUserProvider::class,
    ],
];