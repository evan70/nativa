<?php

declare(strict_types=1);

namespace App\Blog\Repository;

use App\Blog\Entity\Article;
use Marko\Database\Entity\EntityHydrator;
use Marko\Database\Entity\EntityMetadataFactory;
use Marko\Database\Repository\Repository;

/**
 * @extends Repository<Article>
 */
class ArticleRepository extends Repository
{
    protected const string ENTITY_CLASS = Article::class;
}