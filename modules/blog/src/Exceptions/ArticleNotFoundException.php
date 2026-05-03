<?php

declare(strict_types=1);

namespace App\Blog\Exceptions;

use Exception;

class ArticleNotFoundException extends Exception
{
    public function __construct(string $message = 'Article not found')
    {
        parent::__construct($message);
    }
}