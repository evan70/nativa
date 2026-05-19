<?php

declare(strict_types=1);

use App\DatabaseModular\Contracts\ModuleDatabaseResolverInterface;
use Marko\Cardboard\Config\AdminPanelConfig;
use Marko\Cardboard\Config\AdminPanelConfigInterface;
use Marko\Cardboard\Service\PasswordResetService;
use Marko\Core\Container\ContainerInterface;
use Marko\Mail\Contracts\MailerInterface;
use Marko\Notification\Channel\DatabaseChannel;
use Marko\Notification\Channel\MailChannel;
use Marko\Notification\Contracts\ChannelInterface;
use Marko\Notification\NotificationManager;
use Marko\Notification\NotificationSender;

return [
    'bindings' => [
        AdminPanelConfigInterface::class => AdminPanelConfig::class,
        PasswordResetService::class => fn (ContainerInterface $c): PasswordResetService => new PasswordResetService(
            $c->get(ModuleDatabaseResolverInterface::class),
        ),

        // Notification channels
        'notification.channel.mail' => fn (ContainerInterface $c): ChannelInterface => new MailChannel(
            $c->get(MailerInterface::class),
        ),
        'notification.channel.database' => fn (ContainerInterface $c): ChannelInterface => new DatabaseChannel(
            $c->get(\App\Database\CardboardConnection::class)->getConnection(),
        ),

        // Notification manager (singleton)
        NotificationManager::class => function (ContainerInterface $c): NotificationManager {
            $manager = new NotificationManager();
            $manager->register('mail', $c->get('notification.channel.mail'));
            $manager->register('database', $c->get('notification.channel.database'));
            return $manager;
        },

        // Notification sender
        NotificationSender::class => fn (ContainerInterface $c): NotificationSender => new NotificationSender(
            $c->get(NotificationManager::class),
        ),
    ],
];

