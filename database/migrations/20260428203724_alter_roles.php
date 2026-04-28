<?php

declare(strict_types=1);

use Marko\Database\Connection\ConnectionInterface;
use Marko\Database\Migration\Migration;

return new class extends Migration {
    public function up(
        ConnectionInterface $connection,
    ): void {
        // No SQL statements
    }

    public function down(
        ConnectionInterface $connection,
    ): void {
        // No SQL statements
    }
};
