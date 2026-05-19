<?php

declare(strict_types=1);

namespace App\Controller;

use App\Blog\Database\BlogConnection;
use App\Database\CardboardConnection;
use App\Portfolio\Database\PortfolioConnection;
use Marko\Admin\Contracts\AdminSectionRegistryInterface;
use Marko\Authentication\Contracts\GuardInterface;
use Marko\Mark\Middleware\MarkMiddleware;
use Marko\Routing\Attributes\Get;
use Marko\Routing\Attributes\Middleware;
use Marko\Routing\Http\Request;
use Marko\Routing\Http\Response;
use Marko\View\ViewInterface;

#[Middleware(MarkMiddleware::class)]
class MarkDashboardController
{
    public function __construct(
        private readonly ViewInterface $view,
        private readonly AdminSectionRegistryInterface $sectionRegistry,
        private readonly GuardInterface $guard,
        private readonly CardboardConnection $cardboardConnection,
        private readonly BlogConnection $blogConnection,
        private readonly PortfolioConnection $portfolioConnection,
    ) {}

    /**
     * Build menu items from registered sections.
     *
     * @return array<int, array{url: string, label: string, icon: string, active: bool}>
     */
    private function buildMenuItems(string $currentSlug = ''): array
    {
        $items = [];
        foreach ($this->sectionRegistry->all() as $section) {
            $slug = $section->getSlug();
            $items[] = [
                'url' => '/mark' . ($slug !== 'dashboard' ? '/' . $slug : ''),
                'label' => $section->getLabel(),
                'icon' => $section->getIcon(),
                'active' => $slug === $currentSlug,
            ];
        }
        return $items;
    }

    #[Get(path: '/mark')]
    public function index(
        Request $request,
    ): Response {
        $sections = $this->sectionRegistry->all();
        $cardboardDb = $this->cardboardConnection->getConnection();
        $articlesDb = $this->blogConnection->getConnection();
        $portfolioDb = $this->portfolioConnection->getConnection();

        // Real data from database
        $userCount = (int) ($cardboardDb->query('SELECT COUNT(*) as count FROM mark_users')[0]['count'] ?? 0);
        $articleCount = (int) ($articlesDb->query('SELECT COUNT(*) as count FROM articles')[0]['count'] ?? 0);
        $portfolioCount = (int) ($portfolioDb->query('SELECT COUNT(*) as count FROM portfolio_items')[0]['count'] ?? 0);
        $recentUsers = $cardboardDb->query(
            'SELECT id, email, name, "createdAt" FROM mark_users ORDER BY "createdAt" DESC LIMIT 3'
        );

        error_log('[MarkDashboard] Real data: users=' . $userCount . ', articles=' . $articleCount . ', portfolio=' . $portfolioCount);

        return $this->view->render('pages/mark/dashboard', [
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
