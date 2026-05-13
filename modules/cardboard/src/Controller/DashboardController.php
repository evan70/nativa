<?php

declare(strict_types=1);

namespace Marko\Cardboard\Controller;

use App\Blog\Database\BlogConnection;
use App\Database\NativaConnection;
use Marko\Admin\Contracts\AdminSectionRegistryInterface;
use Marko\Mark\Middleware\MarkMiddleware;
use Marko\Authentication\Contracts\GuardInterface;
use Marko\Routing\Attributes\Get;
use Marko\Routing\Attributes\Middleware;
use Marko\Routing\Http\Request;
use Marko\Routing\Http\Response;
use Marko\View\ViewInterface;

class DashboardController
{
    public function __construct(
        private readonly ViewInterface $view,
        private readonly AdminSectionRegistryInterface $sectionRegistry,
        private readonly GuardInterface $guard,
        private readonly NativaConnection $nativaConnection,
        private readonly BlogConnection $blogConnection,
    ) {}

    /**
     * @return array<array{url: string, label: string, icon: string, active: bool}>
     */
    private function buildMenuItems(): array
    {
        $items = [];
        foreach ($this->sectionRegistry->all() as $section) {
            $items[] = [
                'url' => '/mark' . ($section->getSlug() !== 'dashboard' ? '/' . $section->getSlug() : ''),
                'label' => $section->getLabel(),
                'icon' => $section->getIcon(),
                'active' => false,
            ];
        }
        return $items;
    }

    #[Get(path: '/mark')]
    #[Middleware(MarkMiddleware::class)]
    public function index(
        Request $request,
    ): Response {
        $sections = $this->sectionRegistry->all();
        $nativaDb = $this->nativaConnection->getConnection();
        $articlesDb = $this->blogConnection->getConnection();

        // Real data from database
        $userCount = (int) ($nativaDb->query('SELECT COUNT(*) as count FROM mark_users')[0]['count'] ?? 0);
        $articleCount = (int) ($articlesDb->query('SELECT COUNT(*) as count FROM articles')[0]['count'] ?? 0);
        $portfolioCount = (int) ($nativaDb->query('SELECT COUNT(*) as count FROM portfolio_items')[0]['count'] ?? 0);
        $recentUsers = $nativaDb->query(
            'SELECT id, email, name, "createdAt" FROM mark_users ORDER BY "createdAt" DESC LIMIT 3'
        );

        error_log('[Dashboard] Real data: users=' . $userCount . ', articles=' . $articleCount . ', portfolio=' . $portfolioCount);

        return $this->view->render('pages/dash/index', [
            'sections' => $sections,
            'currentUser' => $this->guard->user(),
            'menuItems' => $this->buildMenuItems(),
            'activeSection' => 'dashboard',
            'stats' => [
                'users' => $userCount,
                'articles' => $articleCount,
                'portfolio' => $portfolioCount,
                'activeNow' => $userCount > 0 ? min($userCount, 1) : 0,
            ],
            'recentUsers' => $recentUsers,
        ]);
    }
}