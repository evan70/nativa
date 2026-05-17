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
        ErrorHandlerInterface::class => AdvancedErrorHandler::class,
        // Override TemplateResolver to check app templates first, then modules
        TemplateResolverInterface::class => function (ContainerInterface $container): TemplateResolverInterface {
            $repository = $container->get(\Marko\Core\Module\ModuleRepositoryInterface::class);
            $config = $container->get(\Marko\View\ViewConfig::class);
            
            if (!$repository instanceof \Marko\Core\Module\ModuleRepositoryInterface || !$config instanceof \Marko\View\ViewConfig) {
                throw new \RuntimeException('Invalid DI configuration');
            }
            
            $moduleResolver = new \Marko\View\ModuleTemplateResolver($repository, $config);
            return new AppTemplateResolver($moduleResolver);
        },
        AssetAwareViewInterface::class => function (ContainerInterface $container): AssetAwareViewInterface {
            $resolver = $container->get(\Marko\View\TemplateResolverInterface::class);
            $config = $container->get(\Marko\View\ViewConfig::class);

            if (!$resolver instanceof \Marko\View\TemplateResolverInterface || !$config instanceof \Marko\View\ViewConfig) {
                throw new \RuntimeException('Invalid DI configuration');
            }

            $view = new SimpleView($resolver, $config);

            return new ViewAdapter($view);
        },
        // Also bind ViewInterface to same implementation for code that uses it
        ViewInterface::class => function (ContainerInterface $container) {
            /** @var AssetAwareViewInterface $view */
            $view = $container->get(AssetAwareViewInterface::class);
            return $view;
        },
    ],
    'preferences' => [],
];