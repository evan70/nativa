<?php

declare(strict_types=1);

namespace Marko\Validation\Exceptions;

use Marko\Core\Exceptions\MarkoException;
use Marko\Validation\Validation\ValidationErrors;
use Throwable;

class ValidationException extends MarkoException
{
    public function __construct(
        string $message,
        private readonly ValidationErrors $errors,
        string $context = '',
        string $suggestion = '',
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $context, $suggestion, $code, $previous);
    }

    public static function withErrors(
        ValidationErrors $errors,
    ): self {
        return new self(
            'The given data was invalid.',
            $errors,
            'Validation failed for one or more fields.',
            'Check the errors() method for details on which fields failed.',
        );
    }

    public function errors(): ValidationErrors
    {
        return $this->errors;
    }
}
