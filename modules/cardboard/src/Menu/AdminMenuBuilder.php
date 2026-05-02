<?php

declare(strict_types=1);

namespace Marko\Cardboard\Menu;

use Marko\Admin\Contracts\AdminSectionInterface;
use Marko\Admin\Contracts\AdminSectionRegistryInterface;
use Marko\Admin\Contracts\MenuItemInterface;
use Marko\Mark\Entity\MarkInterface;

class AdminMenuBuilder implements AdminMenuBuilderInterface
{
    public function __construct(
        private readonly AdminSectionRegistryInterface $sectionRegistry,
    ) {}

    /**
     * @return array<int, array{section: AdminSectionInterface, items: array<MenuItemInterface>, active: bool, activeItemId: string|null}>
     */
    public function build(
        MarkInterface $user,
        string $currentPath = '',
    ): array {
        $sections = $this->sectionRegistry->all();
        $menu = [];

        foreach ($sections as $section) {
            $filteredItems = $this->filterItemsByPermission($section->getMenuItems(), $user);
            $sortedItems = $this->sortItems($filteredItems);

            if ($sortedItems === []) {
                continue;
            }

            $sectionActive = false;
            $activeItemId = null;

            foreach ($sortedItems as $item) {
                if ($currentPath !== '' && $item->getUrl() === $currentPath) {
                    $sectionActive = true;
                    $activeItemId = $item->getId();
                }
            }

            $menu[] = [
                'section' => $section,
                'items' => $sortedItems,
                'active' => $sectionActive,
                'activeItemId' => $activeItemId,
            ];
        }

        return $menu;
    }

    /**
     * @return array<AdminSectionInterface>
     */
    public function buildDashboardSections(
        MarkInterface $user,
    ): array {
        $sections = $this->sectionRegistry->all();
        $result = [];

        foreach ($sections as $section) {
            $filteredItems = $this->filterItemsByPermission($section->getMenuItems(), $user);

            if ($filteredItems !== []) {
                $result[] = $section;
            }
        }

        return $result;
    }

    /**
     * @param array<MenuItemInterface> $items
     * @return array<MenuItemInterface>
     */
    private function filterItemsByPermission(
        array $items,
        MarkInterface $user,
    ): array {
        return array_values(array_filter(
            $items,
            static function (MenuItemInterface $item) use ($user): bool {
                $permission = $item->getPermission();

                if ($permission === '') {
                    return true;
                }

                return $user->hasPermission($permission);
            },
        ));
    }

    /**
     * @param array<MenuItemInterface> $items
     * @return array<MenuItemInterface>
     */
    private function sortItems(
        array $items,
    ): array {
        $sorted = $items;

        usort(
            $sorted,
            static fn (MenuItemInterface $a, MenuItemInterface $b): int => $a->getSortOrder() <=> $b->getSortOrder(),
        );

        return $sorted;
    }
}
