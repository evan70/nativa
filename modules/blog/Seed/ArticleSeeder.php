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
        // Clean existing data to avoid duplicates if re-seeded
        $this->connection->execute('DELETE FROM "article_tags"');
        $this->connection->execute('DELETE FROM "articles"');
        $this->connection->execute('DELETE FROM "tags"');
        $this->connection->execute('DELETE FROM "categories"');

        $categories = [
            ['name' => 'Architecture', 'slug' => 'architecture', 'description' => 'System architecture, DDD and design decisions'],
            ['name' => 'Frontend', 'slug' => 'frontend', 'description' => 'UI, components, styling and interaction patterns'],
            ['name' => 'Backend', 'slug' => 'backend', 'description' => 'PHP services, persistence and business logic'],
            ['name' => 'DevOps', 'slug' => 'devops', 'description' => 'Build, tooling, CI and deployment practices'],
        ];

        $tags = [
            ['name' => 'PHP', 'slug' => 'php'],
            ['name' => 'Svelte', 'slug' => 'svelte'],
            ['name' => 'DDD', 'slug' => 'ddd'],
            ['name' => 'Architecture', 'slug' => 'architecture'],
            ['name' => 'Frontend', 'slug' => 'frontend'],
            ['name' => 'BEM', 'slug' => 'bem'],
            ['name' => 'Design Tokens', 'slug' => 'design-tokens'],
            ['name' => 'Refactoring', 'slug' => 'refactoring'],
            ['name' => 'Tooling', 'slug' => 'tooling'],
        ];

        $categoryIds = [];
        foreach ($categories as $category) {
            $this->connection->execute(
                'INSERT INTO "categories" ("name", "slug", "description") VALUES (?, ?, ?)',
                [$category['name'], $category['slug'], $category['description']]
            );
            $categoryIds[$category['slug']] = $this->connection->lastInsertId();
        }

        $tagIds = [];
        foreach ($tags as $tag) {
            $this->connection->execute(
                'INSERT INTO "tags" ("name", "slug") VALUES (?, ?)',
                [$tag['name'], $tag['slug']]
            );
            $tagIds[$tag['slug']] = $this->connection->lastInsertId();
        }

        $welcomeArticle = [
            'title' => 'Welcome to PHP CMS',
            'slug' => 'welcome-to-php-cms',
            'excerpt' => 'Your first article',
            'content' => 'This is your first article. Start creating content!',
            'status' => 'published',
            'image' => 'https://res.cloudinary.com/epithemic/image/upload/v1773169415/blog/afe59aa58f41fc48817094cfe7519d0b_stmn3l.webp',
            'category_slug' => 'backend',
            'tags' => ['php', 'architecture'],
        ];

        $articles = [
            [
                'title' => 'Nativa PHP + Svelte 5 Architektúra',
                'slug' => 'nativa-php-svelte-architektura',
                'excerpt' => 'Moderná DDD architektúra s využitím Svelte 5',
                'content' => 'Naša architektúra kombinuje PHP 8.4+ s DDD prístupom a Svelte 5 komponentmi. Tento prínos nám umožňuje vytvárať rýchle, udržiavateľné a škálovateľné aplikácie.',
                'image' => 'https://res.cloudinary.com/epithemic/image/upload/v1773169416/blog/26d7d834d1eda62fc868808f37c9b157_zdluym.webp',
                'status' => 'published',
                'category_slug' => 'architecture',
                'tags' => ['php', 'svelte', 'ddd', 'architecture'],
            ],
            [
                'title' => 'BEM + Design Tokens v Praxi',
                'slug' => 'bem-design-tokens-prax',
                'excerpt' => 'Konzistentný design systém s BEM metódológiou',
                'content' => 'Implementovali sme design tokens systém s BEM komponentmi pre konzistentný UI/UX naprieč celou aplikáciou. Farby, typography, spacing - všetko je centralizované.',
                'image' => 'https://res.cloudinary.com/epithemic/image/upload/v1773169415/blog/afe59aa58f41fc48817094cfe7519d0b_stmn3l.webp',
                'status' => 'published',
                'category_slug' => 'frontend',
                'tags' => ['bem', 'design-tokens', 'frontend'],
            ],
            [
                'title' => 'Path Value Object Implementácia',
                'slug' => 'path-value-object-implementacia',
                'excerpt' => 'Type-safe práca s cestami v DDD',
                'content' => 'Namiesto stringov používame Path Value Object pre type-safe prácu s cestami. Tento prístup eliminuje chyby a zlepšuje developer experience.',
                'image' => 'https://res.cloudinary.com/epithemic/image/upload/v1773169415/blog/afe59aa58f41fc48817094cfe7519d0b_stmn3l.webp',
                'status' => 'published',
                'category_slug' => 'backend',
                'tags' => ['php', 'ddd', 'refactoring'],
            ],
            [
                'title' => 'Hybrid PHP/Svelte Rendering',
                'slug' => 'hybrid-php-svelte-rendering',
                'excerpt' => 'SEO friendly rendering s progresívnym vylepšením',
                'content' => 'PHP renderuje počiatočný HTML obsah pre SEO, Svelte 5 komponenty potom pridávajú interaktivitu. Best of both worlds!',
                'image' => 'https://res.cloudinary.com/epithemic/image/upload/v1773169416/blog/dae2d1fd9b13c89bb5b4a89280099d7a_hqfarh.webp',
                'status' => 'published',
                'category_slug' => 'frontend',
                'tags' => ['php', 'svelte', 'architecture'],
            ],
            [
                'title' => 'Vite Build optimalizácia',
                'slug' => 'vite-build-optimalizacia',
                'excerpt' => 'Rýchlejší build s menšími bundlami',
                'content' => 'Optimalizovali sme Vite build proces pre lepšiu performance. Code splitting, tree shaking a lazy loading sú teraz štandardom.',
                'image' => 'https://res.cloudinary.com/epithemic/image/upload/v1773169416/blog/d76d493024744f5142823636a88bb4dd_fxcrhd.webp',
                'status' => 'published',
                'category_slug' => 'frontend',
                'tags' => ['tooling', 'frontend', 'refactoring'],
            ],
            [
                'title' => 'SQLite vs MySQL pre CMS',
                'slug' => 'sqlite-vs-mysql-cms',
                'excerpt' => 'Kedy použiť SQLite a kedy MySQL?',
                'content' => 'SQLite je výborný pre development a menšie projekty. MySQL/PostgreSQL sú lepšie pre veľké aplikácie s vysokou záťažou.',
                'image' => 'https://res.cloudinary.com/epithemic/image/upload/v1773169415/blog/d1a18cb5ea2f538c0a8d06e4f6e74264_rdkl7a.webp',
                'status' => 'published',
                'category_slug' => 'backend',
                'tags' => ['php', 'architecture'],
            ],
            [
                'title' => 'Component Library Patterns',
                'slug' => 'component-library-patterns',
                'excerpt' => 'Znovupoužiteľné UI komponenty',
                'content' => 'Správne navrhnutá component library urýchli vývoj a zabezpečí konzistentný UI. Ukážeme si best practices pre tvorbu komponentov.',
                'image' => 'https://res.cloudinary.com/epithemic/image/upload/v1773169415/blog/c492faf34ca219ceccfbde6eedaf2b6b_ulm82d.webp',
                'status' => 'published',
                'category_slug' => 'frontend',
                'tags' => ['frontend', 'bem', 'architecture'],
            ],
            [
                'title' => 'Infrastructure Cleanup - Dead Code Elimination',
                'slug' => 'infrastructure-cleanup-dead-code',
                'excerpt' => 'Odstránenie nepotrebných 1000+ riadkov kódu',
                'content' => 'Po implementácii Path VO sme odstránili celé adresáre (Filesystem, Presets, Security) - viac ako 1000 riadkov nepotrebného kódu.',
                'image' => 'https://res.cloudinary.com/epithemic/image/upload/v1773169415/blog/c492faf34ca219ceccfbde6eedaf2b6b_ulm82d.webp',
                'status' => 'published',
                'category_slug' => 'devops',
                'tags' => ['refactoring', 'tooling', 'architecture'],
            ],
        ];

        array_unshift($articles, $welcomeArticle);

        foreach ($articles as $article) {
            $this->connection->execute(
                'INSERT INTO "articles" ("title", "slug", "excerpt", "content", "image", "status", "category_id", "published", "created_at") VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    $article['title'],
                    $article['slug'],
                    $article['excerpt'],
                    $article['content'],
                    $article['image'],
                    $article['status'],
                    $categoryIds[$article['category_slug']] ?? null,
                    '1',
                    date('Y-m-d H:i:s'),
                ]
            );
            $articleId = $this->connection->lastInsertId();

            foreach ($article['tags'] as $tagSlug) {
                if (isset($tagIds[$tagSlug])) {
                    $this->connection->execute(
                        'INSERT INTO "article_tags" ("article_id", "tag_id") VALUES (?, ?)',
                        [$articleId, $tagIds[$tagSlug]]
                    );
                }
            }
        }
    }
}
