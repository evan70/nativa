<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../packages/database/src/Schema/Column.php';
require __DIR__ . '/../packages/database/src/Schema/ForeignKey.php';
require __DIR__ . '/../packages/database/src/Schema/Index.php';
require __DIR__ . '/../packages/database/src/Schema/IndexType.php';
require __DIR__ . '/../packages/database/src/Schema/Table.php';
require __DIR__ . '/../packages/database/src/Diff/SchemaDiff.php';
require __DIR__ . '/../modules/database-sqlite/src/Connection/SqliteConnection.php';
require __DIR__ . '/../modules/database-sqlite/src/Connection/SqliteException.php';
require __DIR__ . '/../modules/database-sqlite/src/Connection/SqliteStatement.php';
require __DIR__ . '/../modules/database-sqlite/src/Introspection/SqliteIntrospector.php';
require __DIR__ . '/../modules/database-sqlite/src/Diff/SqliteSqlGenerator.php';