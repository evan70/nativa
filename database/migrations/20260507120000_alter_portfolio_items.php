<?php

declare(strict_types=1);

use Marko\Database\Connection\ConnectionInterface;
use Marko\Database\Migration\Migration;

return new class extends Migration {
    public function up(
        ConnectionInterface $connection,
    ): void {
        $this->execute($connection, 'ALTER TABLE "portfolio_items" ADD COLUMN "subtitle" TEXT');
        $this->execute($connection, 'ALTER TABLE "portfolio_items" ADD COLUMN "role" TEXT');
        $this->execute($connection, 'ALTER TABLE "portfolio_items" ADD COLUMN "year" TEXT');
        $this->execute($connection, 'ALTER TABLE "portfolio_items" ADD COLUMN "stack" TEXT');
        $this->execute($connection, 'ALTER TABLE "portfolio_items" ADD COLUMN "display_order" INTEGER NOT NULL DEFAULT 0');
    }

    public function down(
        ConnectionInterface $connection,
    ): void {
        // SQLite does not support dropping columns directly in this migration style.
        // Leave as a no-op to keep rollback safe.
    }
};
