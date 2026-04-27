<?php

declare(strict_types=1);

namespace App\Seed;

use Marko\Database\Connection\ConnectionInterface;
use Marko\Database\Seed\Seeder;
use Marko\Database\Seed\SeederInterface;

#[Seeder(name: 'PortfolioSeeder', order: 3)]
class PortfolioSeeder implements SeederInterface
{
    public function __construct(
        private ConnectionInterface $connection,
    ) {}

    public function run(): void
    {
        $this->connection->execute('DELETE FROM "portfolio_items"');

        $portfolio = [
            [
                'title' => 'E-Commerce Platform',
                'slug' => 'ecommerce-platform',
                'description' => 'Full-stack online store with real-time inventory',
                'category' => 'fullstack',
                'image' => 'https://res.cloudinary.com/epithemic/image/upload/v1773169416/blog/26d7d834d1eda62fc868808f37c9b157_zdluym.webp',
            ],
            [
                'title' => 'Design System',
                'slug' => 'design-system',
                'description' => 'Scalable component library with BEM + tokens',
                'category' => 'frontend',
                'image' => 'https://res.cloudinary.com/epithemic/image/upload/v1773169415/blog/afe59aa58f41fc48817094cfe7519d0b_stmn3l.webp',
            ],
            [
                'title' => 'API Gateway',
                'slug' => 'api-gateway',
                'description' => 'High-performance microservices gateway',
                'category' => 'backend',
                'image' => 'https://res.cloudinary.com/epithemic/image/upload/v1773169416/blog/dae2d1fd9b13c89bb5b4a89280099d7a_hqfarh.webp',
            ],
            [
                'title' => 'Analytics Dashboard',
                'slug' => 'analytics-dashboard',
                'description' => 'Real-time data visualization platform',
                'category' => 'fullstack',
                'image' => 'https://res.cloudinary.com/epithemic/image/upload/v1773169416/blog/d76d493024744f5142823636a88bb4dd_fxcrhd.webp',
            ],
            [
                'title' => 'Mobile Banking App',
                'slug' => 'mobile-banking',
                'description' => 'Secure fintech application with biometric auth',
                'category' => 'fullstack',
                'image' => 'https://res.cloudinary.com/epithemic/image/upload/v1773169415/blog/d1a18cb5ea2f538c0a8d06e4f6e74264_rdkl7a.webp',
            ],
            [
                'title' => 'Component Library',
                'slug' => 'component-library',
                'description' => 'Reusable UI components for enterprise apps',
                'category' => 'frontend',
                'image' => 'https://res.cloudinary.com/epithemic/image/upload/v1773169415/blog/c492faf34ca219ceccfbde6eedaf2b6b_ulm82d.webp',
            ],
        ];

        foreach ($portfolio as $item) {
            $this->connection->execute(
                'INSERT INTO "portfolio_items" ("title", "slug", "description", "category", "image") VALUES (?, ?, ?, ?, ?)',
                [
                    $item['title'],
                    $item['slug'],
                    $item['description'],
                    $item['category'],
                    $item['image'],
                ]
            );
        }
    }
}
