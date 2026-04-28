<?php

declare(strict_types=1);

use Marko\Database\Connection\ConnectionInterface;
use Marko\Database\Migration\DataMigration;

return new class extends DataMigration {
    public function up(
        ConnectionInterface $connection,
    ): void {
        // Insert default admin user (email: admin@marko.local, password: password)
        // Use parent insert method which handles bindings properly
        $this->insert($connection, 'mark_users', [
            'email' => 'admin@marko.local',
            'password' => password_hash('password', PASSWORD_BCRYPT),
            'name' => 'Administrator',
            'rememberToken' => null,
            'isActive' => '1',
            'createdAt' => date('Y-m-d H:i:s'),
            'updatedAt' => date('Y-m-d H:i:s'),
        ]);

        // Get the last insert ID - need to query it
        $result = $connection->query("SELECT last_insert_rowid() as id");
        $userId = (int) ($result[0]['id'] ?? 0);

        // Get admin role ID
        $roles = $connection->query("SELECT id FROM roles WHERE slug = 'admin' LIMIT 1");

        if (!empty($roles)) {
            $this->insert($connection, 'mark_roles', [
                'user_id' => $userId,
                'role_id' => (int) $roles[0]['id'],
            ]);
        }
    }

    public function down(
        ConnectionInterface $connection,
    ): void {
        $connection->execute("DELETE FROM mark_users WHERE email = 'admin@marko.local'");
    }
};
