<?php

declare(strict_types=1);

namespace App\Blog\Exceptions;

use Exception;

class ArticleValidationException extends Exception
{
    public function __construct(string $message = 'Validation failed')
    {
        parent::__construct($message);
    }
}