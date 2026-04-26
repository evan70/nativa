<?php

declare(strict_types=1);

namespace Marko\Sqlite\Diff;

use Marko\Database\Diff\SqlGeneratorInterface;
use Marko\Database\Diff\SchemaDiff;
use Marko\Database\Schema\Column;
use Marko\Database\Schema\ForeignKey;
use Marko\Database\Schema\Index;
use Marko\Database\Schema\IndexType;

class SqliteSqlGenerator implements SqlGeneratorInterface
{
    public function generateUp(SchemaDiff $diff): array
    {
        $statements = [];

        foreach ($diff->tablesToDrop as $table) {
            $statements[] = $this->generateDropTable($table->name);
        }

        foreach ($diff->tablesToCreate as $table) {
            $statements[] = $this->generateCreateTable($table);
            foreach ($table->indexes as $index) {
                $statements[] = $this->generateAddIndex($table->name, $index);
            }
        }

        foreach ($diff->tablesToAlter as $tableName => $tableDiff) {
            foreach ($tableDiff->columnsToAdd as $column) {
                $statements[] = $this->generateAddColumn($tableName, $column);
            }
            // SQLite has limited ALTER TABLE support. 
            // For complex changes, usually a table recreation is needed.
            // For now, we only implement AddColumn.
        }

        return array_filter($statements);
    }

    public function generateDown(SchemaDiff $diff): array
    {
        $statements = [];

        foreach ($diff->tablesToCreate as $table) {
            $statements[] = $this->generateDropTable($table->name);
        }

        return array_filter($statements);
    }

    public function generateCreateTable(\Marko\Database\Schema\Table $table): string
    {
        $columns = [];
        $primaryKey = [];

        foreach ($table->columns as $column) {
            $type = $this->mapType($column);
            $quotedName = $this->quote($column->name);

            if ($column->autoIncrement && $column->primaryKey && $type === 'INTEGER') {
                $colDef = $quotedName . ' INTEGER PRIMARY KEY AUTOINCREMENT';
            } else {
                $colDef = $quotedName . ' ' . $type;

                if (!$column->nullable) {
                    $colDef .= ' NOT NULL';
                }

                if ($column->default !== null) {
                    $colDef .= ' DEFAULT ' . $this->formatDefault($column->default);
                }

                if ($column->primaryKey) {
                    $primaryKey[] = $quotedName;
                }
            }

            $columns[] = $colDef;
        }

        if (!empty($primaryKey)) {
            $columns[] = 'PRIMARY KEY (' . implode(', ', $primaryKey) . ')';
        }

        return sprintf(
            'CREATE TABLE %s (%s)',
            $this->quote($table->name),
            implode(', ', $columns),
        );
    }

    public function generateDropTable(string $tableName): string
    {
        return "DROP TABLE IF EXISTS " . $this->quote($tableName);
    }

    public function generateAddColumn(string $table, Column $column): string
    {
        $type = $this->mapType($column);
        $colDef = $this->quote($column->name) . ' ' . $type;

        if (!$column->nullable) {
            $colDef .= ' NOT NULL';
        }

        if ($column->default !== null) {
            $colDef .= ' DEFAULT ' . $this->formatDefault($column->default);
        }

        return "ALTER TABLE " . $this->quote($table) . " ADD COLUMN $colDef";
    }

    public function generateDropColumn(string $table, string $columnName): string
    {
        return "ALTER TABLE " . $this->quote($table) . " DROP COLUMN " . $this->quote($columnName);
    }

    public function generateModifyColumn(
        string $table,
        Column $column,
        Column $oldColumn,
    ): string {
        return "ALTER TABLE " . $this->quote($table) . " MODIFY COLUMN " . $this->quote($column->name) . ' ' . $this->mapType($column);
    }

    public function generateAddIndex(string $table, Index $index): string
    {
        $columns = implode(', ', array_map([$this, 'quote'], $index->columns));
        $unique = $index->type === IndexType::Unique ? 'UNIQUE ' : '';

        return "CREATE {$unique}INDEX " . $this->quote($index->name) . " ON " . $this->quote($table) . " ($columns)";
    }

    public function generateDropIndex(string $table, string $indexName): string
    {
        return "DROP INDEX " . $this->quote($indexName);
    }

    public function generateAddForeignKey(string $table, ForeignKey $foreignKey): string
    {
        $column = $this->quote($foreignKey->columns[0] ?? '');
        $referencedColumn = $this->quote($foreignKey->referencedColumns[0] ?? '');
        
        return "ALTER TABLE " . $this->quote($table) . " ADD FOREIGN KEY ($column) REFERENCES " . $this->quote($foreignKey->referencedTable) . "($referencedColumn)";
    }

    public function generateDropForeignKey(string $table, string $keyName): string
    {
        return "ALTER TABLE " . $this->quote($table) . " DROP FOREIGN KEY " . $this->quote($keyName);
    }

    private function mapType(Column $column): string
    {
        return match (strtoupper($column->type)) {
            'INTEGER', 'INT' => 'INTEGER',
            'BIGINT' => 'BIGINT',
            'SMALLINT' => 'INTEGER',
            'TINYINT' => 'INTEGER',
            'DOUBLE', 'DECIMAL' => 'REAL',
            'FLOAT' => 'REAL',
            'BLOB' => 'BLOB',
            default => 'TEXT',
        };
    }

    private function formatDefault(mixed $default): string
    {
        if ($default === 'NULL') {
            return 'NULL';
        }

        if (is_bool($default)) {
            return $default ? '1' : '0';
        }

        if (is_numeric($default)) {
            return (string) $default;
        }

        return "'" . $default . "'";
    }

    private function quote(string $identifier): string
    {
        return '"' . str_replace('"', '""', $identifier) . '"';
    }
}
