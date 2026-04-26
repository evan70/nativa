<?php

declare(strict_types=1);

use Marko\Core\Container\ContainerInterface;
use Marko\Core\Path\ProjectPaths;
use Marko\Database\Connection\ConnectionInterface;
use Marko\Database\Connection\TransactionInterface;
use Marko\Database\Diff\SqlGeneratorInterface;
use Marko\Database\Introspection\IntrospectorInterface;
use Marko\Sqlite\Connection\SqliteConnection;
use Marko\Sqlite\Diff\SqliteSqlGenerator;
use Marko\Sqlite\Introspection\SqliteIntrospector;

$basePath = dirname(__DIR__, 2);

return [
    'bindings' => [
        ConnectionInterface::class => function () use ($basePath): SqliteConnection {
            $dbPath = ':memory:';
            return new SqliteConnection($dbPath);
        },
        TransactionInterface::class => function () use ($basePath): SqliteConnection {
            $dbPath = ':memory:';
            return new SqliteConnection($dbPath);
        },
        IntrospectorInterface::class => function (ContainerInterface $container): SqliteIntrospector {
            $connection = $container->get(ConnectionInterface::class);
            return new SqliteIntrospector($connection);
        },
        SqlGeneratorInterface::class => SqliteSqlGenerator::class,
    ],
    'boot' => function (ConnectionInterface $connection): void {
        $connection->connect();
    },
];