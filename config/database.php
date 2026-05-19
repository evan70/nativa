<?php

declare(strict_types=1);

return [
    'driver' => 'sqlite',
    'database' => dirname(__DIR__) . '/storage/data/cardboard.db',
    
    // Mapovanie modulov na databázy
    // Kľúč = názov modulu (adresár v modules/)
    // Hodnota = názov databázy (bez .db prípony)
    'modules' => [
        'blog' => 'articles',
        'cardboard' => 'cardboard',
        'mark' => 'cardboard',
        'portfolio' => 'portfolio',
    ],
];
