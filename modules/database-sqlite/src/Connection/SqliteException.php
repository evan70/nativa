<?php

declare(strict_types=1);

namespace Marko\Database\Sqlite\Connection;

use Marko\Core\Exceptions\MarkoException;
use Throwable;

class SqliteException extends MarkoException
{
    public function __construct(
        string $message = '',
        string $context = '',
        string $suggestion = '',
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            message: $message,
            context: $context,
            suggestion: $suggestion,
            previous: $previous,
        );
    }
}