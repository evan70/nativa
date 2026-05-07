<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Entity\PortfolioItem;
use App\Repository\PortfolioItemRepository;
use Marko\Routing\Attributes\Get;
use Marko\Routing\Http\Request;
use Marko\Routing\Http\Response;
use Marko\View\ViewInterface;

readonly class PortfolioController
{
    public function __construct(
        private readonly PortfolioItemRepository $repository,
        private readonly ViewInterface $view,
    ) {}

    #[Get(path: '/portfolio')]
    public function index(Request $request): Response
    {
        if ($this->shouldLogDebug()) {
            $this->logDebug('PortfolioController::index called', ['path' => $request->path()]);
        }

        return $this->view
            ->withAssets('app/portfolio', [], ['portfolio-css'])
            ->render('app.portfolio', [
                'title' => 'Portfolio | Nativa',
                'eyebrow' => 'Our Work',
                'heading' => 'Selected Projects',
                'description' => 'A collection of projects built with vanilla performance and BEM architecture.',
                'projects' => $this->loadProjects(),
            ]);
    }

    #[Get(path: '/portfolio/{slug}')]
    public function show(string $slug): Response
    {
        $project = $this->findPortfolioItem($slug);
        $status = $project === null ? 404 : 200;

        if ($this->shouldLogDebug()) {
            $this->logDebug('PortfolioController::show called', [
                'slug' => $slug,
                'found' => $project !== null,
            ]);
        }

        return $this->view
            ->withAssets('app/portfolio', [], ['portfolio-css'])
            ->render('app.portfolio-show', [
                'title' => $project?->title ?? 'Project not found | Nativa',
                'eyebrow' => $project === null ? 'Our Work' : 'Case Study',
                'heading' => $project?->title ?? 'Project not found',
                'description' => $project?->description ?? 'The requested project does not exist.',
                'project' => $project,
                'projects' => $this->loadProjects(),
            ])
            ->withStatus($status);
    }

    /**
     * @return array<int, PortfolioItem>
     */
    private function loadProjects(): array
    {
        $projects = $this->repository->findAll();

        if ($projects === []) {
            $projects = $this->defaultProjects();
        }

        usort($projects, static function (PortfolioItem $left, PortfolioItem $right): int {
            return ($left->displayOrder <=> $right->displayOrder)
                ?: strcmp($left->title, $right->title);
        });

        return $projects;
    }

    private function findPortfolioItem(string $slug): ?PortfolioItem
    {
        $item = $this->repository->findOneBy(['slug' => $slug]);

        if ($item instanceof PortfolioItem) {
            return $item;
        }

        foreach ($this->defaultProjects() as $project) {
            if ($project->slug === $slug) {
                return $project;
            }
        }

        return null;
    }

    /**
     * @return array<int, PortfolioItem>
     */
    private function defaultProjects(): array
    {
        $projects = [];

        foreach ([
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
                'displayOrder' => 10,
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
                'displayOrder' => 20,
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
                'displayOrder' => 30,
            ],
        ] as $data) {
            $item = new PortfolioItem();
            $item->title = $data['title'];
            $item->slug = $data['slug'];
            $item->subtitle = $data['subtitle'];
            $item->description = $data['description'];
            $item->category = $data['category'];
            $item->role = $data['role'];
            $item->year = $data['year'];
            $item->stack = $data['stack'];
            $item->image = $data['image'];
            $item->displayOrder = $data['displayOrder'];

            $projects[] = $item;
        }

        return $projects;
    }

    private function shouldLogDebug(): bool
    {
        return getenv('LOG_LEVEL') === 'debug';
    }

    private function logDebug(string $message, array $context = []): void
    {
        if (function_exists('log_add_debug')) {
            log_add_debug($message, $context);
        }
    }
}
