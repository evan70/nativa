<?php

declare(strict_types=1);

namespace Marko\Cardboard\Controller;

use App\Blog\Database\BlogConnection;
use App\Database\CardboardConnection;
use App\Portfolio\Database\PortfolioConnection;
use Marko\Authentication\Contracts\GuardInterface;
use Marko\Routing\Http\Request;
use Marko\Routing\Http\Response;
use Marko\View\ViewInterface;

class DashboardController
{
    public function __construct(
        private readonly ViewInterface $view,
        private readonly GuardInterface $guard,
        private readonly CardboardConnection $cardboardConnection,
        private readonly BlogConnection $blogConnection,
        private readonly PortfolioConnection $portfolioConnection,
    ) {}

    public function index(
        Request $request,
    ): Response {
        $cardboardDb = $this->cardboardConnection->getConnection();
        $articlesDb = $this->blogConnection->getConnection();
        $portfolioDb = $this->portfolioConnection->getConnection();

        // Real data from database
        $userCountRows = $cardboardDb->query('SELECT COUNT(*) as count FROM mark_users');
        $userCountVal = $userCountRows[0]['count'] ?? null;
        $userCount = is_numeric($userCountVal) ? (int) $userCountVal : 0;
        $articleCountRows = $articlesDb->query('SELECT COUNT(*) as count FROM articles');
        $articleCountVal = $articleCountRows[0]['count'] ?? null;
        $articleCount = is_numeric($articleCountVal) ? (int) $articleCountVal : 0;
        $portfolioCountRows = $portfolioDb->query('SELECT COUNT(*) as count FROM portfolio_items');
        $portfolioCountVal = $portfolioCountRows[0]['count'] ?? null;
        $portfolioCount = is_numeric($portfolioCountVal) ? (int) $portfolioCountVal : 0;
        $recentUsers = $cardboardDb->query(
            'SELECT id, email, name, "createdAt" FROM mark_users ORDER BY "createdAt" DESC LIMIT 3'
        );

        error_log('[Dashboard Legacy] Real data: users=' . $userCount . ', articles=' . $articleCount . ', portfolio=' . $portfolioCount);

        return $this->view->render('pages/mark/dashboard', [
            'currentUser' => $this->guard->user(),
            'menuItems' => [],
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