<?php

use App\ViewSimple\SimpleView;
use Marko\View\ViewInterface;

return [
    'bindings' => [
        ViewInterface::class => SimpleView::class,
    ],
];
