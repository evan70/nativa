<?php

declare(strict_types=1);

namespace App\Portfolio\Repository;

use App\Portfolio\Entity\PortfolioItem;
use Marko\Database\Repository\Repository;

/**
 * @extends Repository<PortfolioItem>
 */
class PortfolioItemRepository extends Repository
{
    protected const string ENTITY_CLASS = PortfolioItem::class;
}
