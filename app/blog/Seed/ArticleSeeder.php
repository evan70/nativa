<?php

declare(strict_types=1);

namespace App\Blog\Seed;

use Marko\Database\Connection\ConnectionInterface;
use Marko\Database\Seed\Seeder;
use Marko\Database\Seed\SeederInterface;

#[Seeder(name: 'ArticleSeeder', order: 2)]
class ArticleSeeder implements SeederInterface
{
    public function __construct(
        private ConnectionInterface $connection,
    ) {}

    public function run(): void
    {
        $articles = [
            [
                'title' => 'Welcome to Marko',
                'content' => 'This is your first article in the Marko framework.',
                'published' => '1',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'title' => 'Loud Errors Philosophy',
                'content' => 'Marko believes that errors should be loud and helpful.',
                'published' => '1',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'title' => 'Getting Started with Seeders',
                'content' => 'Seeders are a great way to populate your database with initial data.',
                'published' => '1',
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($articles as $article) {
            $this->connection->execute(
                'INSERT INTO "articles" ("title", "content", "published", "created_at") VALUES (?, ?, ?, ?)',
                [
                    $article['title'],
                    $article['content'],
                    $article['published'],
                    $article['created_at'],
                ]
            );
        }
    }
}
