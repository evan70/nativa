<?php

declare(strict_types=1);

namespace App\Portfolio\Seed;

use Marko\Database\Connection\ConnectionInterface;
use Marko\Database\Seed\Seeder;
use Marko\Database\Seed\SeederInterface;

#[Seeder(name: 'PortfolioSeeder', order: 1)]
class PortfolioSeeder implements SeederInterface
{
    public function __construct(
        private readonly ConnectionInterface $connection,
    ) {}

    public function run(): void
    {
        $this->connection->execute('DELETE FROM "portfolio_items"');

        $items = [
            [
                'title' => 'Analytics Dashboard',
                'slug' => 'analytics-dashboard',
                'subtitle' => 'Data visualization platform',
                'description' => 'Real-time metrics and insights for enterprise clients. Built with vanilla TypeScript and custom charting components.',
                'category' => 'Dashboard',
                'role' => 'Frontend lead',
                'year' => '2026',
                'stack' => 'TypeScript, Charts, API',
                'image' => '/dist/assets/images/26d7d834d1eda62fc868808f37c9b157.webp',
                'display_order' => 10,
            ],
            [
                'title' => 'E-Commerce API',
                'slug' => 'e-commerce-api',
                'subtitle' => 'Headless commerce backend',
                'description' => 'RESTful API powering a multi-vendor marketplace. Modular architecture with event-driven inventory management.',
                'category' => 'Commerce',
                'role' => 'Backend architecture',
                'year' => '2025',
                'stack' => 'PHP, Marko, SQLite',
                'image' => '/dist/assets/images/afe59aa58f41fc48817094cfe7519d0b.webp',
                'display_order' => 20,
            ],
            [
                'title' => 'CMS Platform',
                'slug' => 'cms-platform',
                'subtitle' => 'Content management system',
                'description' => 'Flexible content authoring with block-based editing. Multi-tenant support and role-based access control.',
                'category' => 'CMS',
                'role' => 'Product engineer',
                'year' => '2025',
                'stack' => 'React, Node.js, PostgreSQL',
                'image' => '/dist/assets/images/c492faf34ca219ceccfbde6eedaf2b6b.webp',
                'display_order' => 30,
            ],
            [
                'title' => 'DevOps Toolkit',
                'slug' => 'devops-toolkit',
                'subtitle' => 'CI/CD automation suite',
                'description' => 'Automated deployment pipelines with rollback support. GitHub Actions integration and environment monitoring.',
                'category' => 'DevOps',
                'role' => 'Delivery automation',
                'year' => '2026',
                'stack' => 'Docker, GitHub Actions, Bash',
                'image' => '/dist/assets/images/d1a18cb5ea2f538c0a8d06e4f6e74264.webp',
                'display_order' => 40,
            ],
            [
                'title' => 'Mobile SDK',
                'slug' => 'mobile-sdk',
                'subtitle' => 'Cross-platform toolkit',
                'description' => 'Native modules for iOS and Android with a unified JavaScript bridge. Offline-first architecture and sync engine.',
                'category' => 'Mobile',
                'role' => 'Platform tooling',
                'year' => '2026',
                'stack' => 'Swift, Kotlin, React Native',
                'image' => '/dist/assets/images/d76d493024744f5142823636a88bb4dd.webp',
                'display_order' => 50,
            ],
        ];

        foreach ($items as $item) {
            $this->connection->execute(
                'INSERT INTO "portfolio_items" ("title", "slug", "subtitle", "description", "category", "role", "year", "stack", "image", "display_order") VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    $item['title'],
                    $item['slug'],
                    $item['subtitle'],
                    $item['description'],
                    $item['category'],
                    $item['role'],
                    $item['year'],
                    $item['stack'],
                    $item['image'],
                    $item['display_order'],
                ]
            );
        }
    }
}