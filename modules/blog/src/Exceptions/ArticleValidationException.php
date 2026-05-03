<?php

declare(strict_types=1);

namespace App\Blog\Exceptions;

use Marko\Exceptions\HttpException;

class ArticleValidationException extends HttpException
{
    public function __construct(string $message = 'Validation failed', ?\Throwable $previous = null)
    {
        parent::__construct($message, 422, $previous);
    }
}