<?php

declare(strict_types=1);

use Marko\Errors\Contracts\ErrorHandlerInterface;
use Marko\ErrorsAdvanced\AdvancedErrorHandler;

return [
    'bindings' => [
        ErrorHandlerInterface::class => AdvancedErrorHandler::class,
    ],
];
