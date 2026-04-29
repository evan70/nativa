<?php

declare(strict_types=1);

namespace Marko\Cardboard\Controller;

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
        
        return $this->view->render('cardboard::dashboard/index', [
            'sections' => $sections,
            'currentUser' => $this->guard->user(),
            'menuItems' => $this->buildMenuItems(),
            'activeSection' => 'dashboard',
        ]);
    }
}
