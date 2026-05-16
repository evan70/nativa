<?php

declare(strict_types=1);

namespace Marko\Mark\Controller;

use App\Blog\Database\BlogConnection;
use App\Portfolio\Database\PortfolioConnection;
use Marko\Mark\Database\MarkConnection;
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
        private readonly MarkConnection $markConnection,
        private readonly BlogConnection $blogConnection,
        private readonly PortfolioConnection $portfolioConnection,
    ) {}

    /**
     * @return array<array{url: string, label: string, icon: string, active: bool}>
     */
    private function buildMenuItems(): array
    {
        $items = [];
        foreach ($this->sectionRegistry->all() as $section) {
            $id = $section->getId();
            $items[] = [
                'url' => '/mark' . ($id !== 'dashboard' ? '/' . $id : ''),
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
        error_log('[Dashboard] Accessing mark dashboard');
        
        $sections = $this->sectionRegistry->all();
        $markDb = $this->markConnection->getConnection();
        $articlesDb = $this->blogConnection->getConnection();
        $portfolioDb = $this->portfolioConnection->getConnection();

        // Real data from database
        try {
            /** @var array<array{count: int|string}> $userCountResult */
            $userCountResult = $markDb->query('SELECT COUNT(*) as count FROM mark_users');
            $userCount = (int) ($userCountResult[0]['count'] ?? 0);
            
            /** @var array<array{id: int|string, email: string, name: string, createdAt: string}> $recentUsers */
            $recentUsers = $markDb->query(
                'SELECT id, email, name, "createdAt" FROM mark_users ORDER BY "createdAt" DESC LIMIT 3'
            );
        } catch (\Exception $e) {
            error_log('[Dashboard] Error fetching users: ' . $e->getMessage());
            $userCount = 0;
            $recentUsers = [];
        }

        try {
            /** @var array<array{count: int|string}> $articleCountResult */
            $articleCountResult = $articlesDb->query('SELECT COUNT(*) as count FROM articles');
            $articleCount = (int) ($articleCountResult[0]['count'] ?? 0);
        } catch (\Exception $e) {
            error_log('[Dashboard] Error fetching articles: ' . $e->getMessage());
            $articleCount = 0;
        }

        try {
            /** @var array<array{count: int|string}> $portfolioCountResult */
            $portfolioCountResult = $portfolioDb->query('SELECT COUNT(*) as count FROM portfolio_items');
            $portfolioCount = (int) ($portfolioCountResult[0]['count'] ?? 0);
        } catch (\Exception $e) {
            error_log('[Dashboard] Error fetching portfolio: ' . $e->getMessage());
            $portfolioCount = 0;
        }

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
