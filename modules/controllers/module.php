<?php

declare(strict_types=1);

use App\AppTemplateResolver;
use App\Contracts\AssetAwareViewInterface;
use App\ViewAdapter;
use App\ViewSimple\SimpleView;
use Marko\Core\Container\ContainerInterface;
use Marko\Errors\Contracts\ErrorHandlerInterface;
use Marko\ErrorsAdvanced\AdvancedErrorHandler;
use Marko\View\TemplateResolverInterface;
use App\Mail\LogMailer;
use Marko\Mail\Contracts\MailerInterface;
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
            // Mail: LogMailer for development (writes to storage/logs/mail.log)
        MailerInterface::class => function (ContainerInterface $container): MailerInterface {
            $config = [];
            if (\class_exists(\Marko\Mail\Config\MailConfig::class) && $container->has(\Marko\Config\ConfigRepositoryInterface::class)) {
                /** @var \Marko\Mail\Config\MailConfig $mailConfig */
                $mailConfig = $container->get(\Marko\Mail\Config\MailConfig::class);
                $config = $mailConfig->driverConfig('log');
            }
            return new LogMailer($config);
        },
    ],
    'sequence' => [
        'after' => ['app/view-simple', 'app/init'],
    ],
];