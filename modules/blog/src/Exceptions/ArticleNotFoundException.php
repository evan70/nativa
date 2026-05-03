<?php

declare(strict_types=1);

namespace App\Blog\Exceptions;

use Marko\Exceptions\HttpException;

class ArticleNotFoundException extends HttpException
{
    public function __construct(string $message = 'Article not found', ?\Throwable $previous = null)
    {
        parent::__construct($message, 404, $previous);
    }
}