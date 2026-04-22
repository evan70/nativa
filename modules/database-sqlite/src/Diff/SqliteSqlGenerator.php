<?php

declare(strict_types=1);

namespace Marko\Sqlite\Diff;

use Marko\Database\Diff\SqlGeneratorInterface;
use Marko\Database\Diff\SchemaDiff;
use Marko\Database\Schema\Column;
use Marko\Database\Schema\ForeignKey;
use Marko\Database\Schema\Index;

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
            $colDef = $column->name . ' ' . $this->mapType($column);

            if (!$column->nullable) {
                $colDef .= ' NOT NULL';
            }

            if ($column->autoIncrement) {
                $colDef .= ' AUTOINCREMENT';
            }

            if ($column->default !== null) {
                $colDef .= ' DEFAULT ' . $this->formatDefault($column->default);
            }

            $columns[] = $colDef;

            if ($column->primaryKey) {
                $primaryKey[] = $column->name;
            }
        }

        if (!empty($primaryKey)) {
            $columns[] = 'PRIMARY KEY (' . implode(', ', $primaryKey) . ')';
        }

        foreach ($table->indexes as $index) {
            $idxCols = implode(', ', $index->columns);
            $columns[] = "INDEX ($idxCols)";
        }

        return sprintf(
            'CREATE TABLE %s (%s)',
            $table->name,
            implode(', ', $columns),
        );
    }

    public function generateDropTable(string $tableName): string
    {
        return "DROP TABLE IF EXISTS $tableName";
    }

    public function generateAddColumn(string $table, Column $column): string
    {
        $colDef = $column->name . ' ' . $this->mapType($column);

        if (!$column->nullable) {
            $colDef .= ' NOT NULL';
        }

        if ($column->default !== null) {
            $colDef .= ' DEFAULT ' . $this->formatDefault($column->default);
        }

        return "ALTER TABLE $table ADD COLUMN $colDef";
    }

    public function generateDropColumn(string $table, string $columnName): string
    {
        return "ALTER TABLE $table DROP COLUMN $columnName";
    }

    public function generateModifyColumn(
        string $table,
        Column $column,
        Column $oldColumn,
    ): string {
        return "ALTER TABLE $table MODIFY COLUMN " . $column->name . ' ' . $this->mapType($column);
    }

    public function generateAddIndex(string $table, Index $index): string
    {
        $columns = implode(', ', $index->columns);

        return "CREATE INDEX {$index->name} ON $table ($columns)";
    }

    public function generateDropIndex(string $table, string $indexName): string
    {
        return "DROP INDEX $indexName";
    }

    public function generateAddForeignKey(string $table, ForeignKey $foreignKey): string
    {
        $column = $foreignKey->columns[0] ?? '';
        $referencedColumn = $foreignKey->referencedColumns[0] ?? '';
        
        return "ALTER TABLE $table ADD FOREIGN KEY ($column) REFERENCES {$foreignKey->referencedTable}($referencedColumn)";
    }

    public function generateDropForeignKey(string $table, string $keyName): string
    {
        return "ALTER TABLE $table DROP FOREIGN KEY $keyName";
    }

    private function mapType(Column $column): string
    {
        return match ($column->type) {
            'INT' => 'INTEGER',
            'BIGINT' => 'BIGINT',
            'SMALLINT' => 'INTEGER',
            'TINYINT' => 'INTEGER',
            'DOUBLE' => 'REAL',
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

        if (is_numeric($default)) {
            return (string) $default;
        }

        return "'" . $default . "'";
    }
}