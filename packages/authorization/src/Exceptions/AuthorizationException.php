<?php

declare(strict_types=1);

namespace Marko\Authorization\Exceptions;

use Marko\Core\Exceptions\MarkoException;
use Throwable;

class AuthorizationException extends MarkoException
{
    public function __construct(
        string $message,
        private readonly string $ability = '',
        private readonly string $resource = '',
        string $context = '',
        string $suggestion = '',
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            $message,
            $context,
            $suggestion,
            $code,
            $previous,
        );
    }

    public function getAbility(): string
    {
        return $this->ability;
    }

    public function getResource(): string
    {
        return $this->resource;
    }

    public static function forbidden(
        string $ability,
        string $resource,
    ): self {
        return new self(
            message: 'Forbidden',
            ability: $ability,
            resource: $resource,
            context: "Unable to perform '$ability' on '$resource'",
            suggestion: 'You do not have permission to perform this action',
        );
    }

    public static function missingPolicy(
        string $entityClass,
        string $ability,
    ): self {
        return new self(
            message: "No policy registered for '$entityClass'",
            ability: $ability,
            resource: $entityClass,
            context: "Attempted to check ability '$ability' on entity '$entityClass' but no policy is registered",
            suggestion: "Register a policy for '$entityClass' using Gate::policy($entityClass, YourPolicyClass::class)",
        );
    }
}
