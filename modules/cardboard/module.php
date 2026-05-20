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
        PasswordResetService::class => function (ContainerInterface $c): PasswordResetService {
            /** @var ModuleDatabaseResolverInterface $resolver */
            $resolver = $c->get(ModuleDatabaseResolverInterface::class);
            return new PasswordResetService($resolver);
        },

        // Notification channels
        'notification.channel.mail' => function (ContainerInterface $c): ChannelInterface {
            /** @var MailerInterface $mailer */
            $mailer = $c->get(MailerInterface::class);
            return new MailChannel($mailer);
        },
        'notification.channel.database' => function (ContainerInterface $c): ChannelInterface {
            /** @var \App\Database\CardboardConnection $cardboard */
            $cardboard = $c->get(\App\Database\CardboardConnection::class);
            return new DatabaseChannel($cardboard->getConnection());
        },

        // Notification manager (singleton)
        NotificationManager::class => function (ContainerInterface $c): NotificationManager {
            $manager = new NotificationManager();
            /** @var ChannelInterface $mailChannel */
            $mailChannel = $c->get('notification.channel.mail');
            $manager->register('mail', $mailChannel);
            /** @var ChannelInterface $dbChannel */
            $dbChannel = $c->get('notification.channel.database');
            $manager->register('database', $dbChannel);
            return $manager;
        },

        // Notification sender
        NotificationSender::class => function (ContainerInterface $c): NotificationSender {
            /** @var NotificationManager $notificationManager */
            $notificationManager = $c->get(NotificationManager::class);
            return new NotificationSender($notificationManager);
        },
    ],
];

