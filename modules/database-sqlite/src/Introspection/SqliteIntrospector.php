<?php

declare(strict_types=1);

namespace Marko\Database\Sqlite\Introspection;

use Marko\Database\Connection\ConnectionInterface;
use Marko\Database\Introspection\IntrospectorInterface;
use Marko\Database\Schema\Column;
use Marko\Database\Schema\ForeignKey;
use Marko\Database\Schema\Index;
use Marko\Database\Schema\Table;
use Marko\Database\Sqlite\Connection\SqliteConnection;
use PDO;

class SqliteIntrospector implements IntrospectorInterface
{
    public function __construct(
        private readonly ConnectionInterface $connection,
    ) {}

    /**
     * @return list<string>
     */
    public function getTables(): array
    {
        $tables = $this->connection->query(
            "SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name",
        );

        /** @var list<string> $names */
        $names = array_column($tables, 'name');
        return $names;
    }

    public function getTable(string $name): ?Table
    {
        if (!$this->tableExists($name)) {
            return null;
        }

        return new Table(
            name: $name,
            columns: $this->getColumns($name),
            indexes: $this->getIndexes($name),
            foreignKeys: $this->getForeignKeys($name),
        );
    }

    public function tableExists(string $name): bool
    {
        $result = $this->connection->query(
            "SELECT 1 FROM sqlite_master WHERE type='table' AND name = ?",
            [$name],
        );

        return !empty($result);
    }

    public function getColumns(string $table): array
    {
        $columns = [];

        /** @var array{name: string, type: string, notnull: int, dflt_value: string|null, pk: int} $info */
        foreach ($this->connection->query("PRAGMA table_info($table)") as $info) {
            $isPk = $info['pk'] === 1;
            $columns[] = new Column(
                name: $info['name'],
                type: $this->mapSqliteType($info['type']),
                nullable: $info['notnull'] === 0,
                default: $info['dflt_value'],
                primaryKey: $isPk,
                autoIncrement: $isPk,
            );
        }

        return $columns;
    }

    public function getIndexes(string $table): array
    {
        $indexes = [];

        /** @var array{name: string, unique: int, origin: string, columns: string} $info */
        foreach ($this->connection->query("PRAGMA index_list($table)") as $info) {
            $columns = [];
            /** @var array{name: string} $col */
            foreach ($this->connection->query("PRAGMA index_info({$info['name']})") as $col) {
                $columns[] = (string) $col['name'];
            }

            $indexes[] = new Index(
                name: $info['name'],
                columns: $columns,
            );
        }

        return $indexes;
    }

    public function getForeignKeys(string $table): array
    {
        $foreignKeys = [];

        /** @var array{from: string, table: string, to: string, on_delete: string, on_update: string} $info */
        foreach ($this->connection->query("PRAGMA foreign_key_list($table)") as $info) {
            $foreignKeys[] = new ForeignKey(
                name: 'fk_' . $info['from'],
                columns: [$info['from']],
                referencedTable: $info['table'],
                referencedColumns: [$info['to']],
                onDelete: $info['on_delete'],
                onUpdate: $info['on_update'],
            );
        }

        return $foreignKeys;
    }

    public function getPrimaryKey(string $table): array
    {
        $primaryKey = [];

        /** @var array{cid: int, name: string, pk: int} $info */
        foreach ($this->connection->query("PRAGMA table_info($table)") as $info) {
            if ($info['pk'] === 1) {
                $primaryKey[] = $info['name'];
            }
        }

        return $primaryKey;
    }

    private function mapSqliteType(string $sqliteType): string
    {
        $type = strtoupper($sqliteType);

        return match (true) {
            str_starts_with($type, 'INT') => 'INT',
            str_starts_with($type, 'TINYINT')
                || str_starts_with($type, 'SMALLINT')
                || str_starts_with($type, 'MEDIUMINT')
                || str_starts_with($type, 'BIGINT')
                || str_starts_with($type, 'UNSIGNED') => 'BIGINT',
            str_starts_with($type, 'REAL')
                || str_starts_with($type, 'DOUBLE')
                || str_starts_with($type, 'FLOAT') => 'DOUBLE',
            str_starts_with($type, 'BLOB') => 'BLOB',
            default => 'VARCHAR',
        };
    }
}