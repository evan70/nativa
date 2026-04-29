<?php

declare(strict_types=1);

namespace Tests\Diff;

use Marko\Database\Diff\SchemaDiff;
use Marko\Database\Schema\Column;
use Marko\Database\Schema\ForeignKey;
use Marko\Database\Schema\Index;
use Marko\Database\Schema\IndexType;
use Marko\Database\Schema\Table;
use Marko\Database\Sqlite\Diff\SqliteSqlGenerator;

describe('SqliteSqlGenerator', function (): void {
    beforeEach(function (): void {
        $this->generator = new SqliteSqlGenerator();
    });

    describe('generateCreateTable', function (): void {
        it('generates CREATE TABLE statement', function (): void {
            $table = new Table(
                name: 'users',
                columns: [
                    new Column(name: 'id', type: 'INT', primaryKey: true, autoIncrement: true),
                    new Column(name: 'name', type: 'VARCHAR', length: 255, nullable: false),
                ],
            );

            $sql = $this->generator->generateCreateTable($table);

            expect($sql)->toContain('CREATE TABLE "users"');
            expect($sql)->toContain('"id" INTEGER PRIMARY KEY AUTOINCREMENT');
            expect($sql)->toContain('"name" TEXT NOT NULL');
        });

        it('includes DEFAULT values', function (): void {
            $table = new Table(
                name: 'posts',
                columns: [
                    new Column(name: 'id', type: 'INT', primaryKey: true, autoIncrement: true),
                    new Column(name: 'published', type: 'BOOLEAN', default: false),
                ],
            );

            $sql = $this->generator->generateCreateTable($table);

            expect($sql)->toContain('DEFAULT 0');
        });
    });

    describe('generateDropTable', function (): void {
        it('generates DROP TABLE statement', function (): void {
            $sql = $this->generator->generateDropTable('users');

            expect($sql)->toBe('DROP TABLE IF EXISTS "users"');
        });
    });

    describe('generateAddColumn', function (): void {
        it('generates ALTER TABLE ADD COLUMN', function (): void {
            $column = new Column(name: 'bio', type: 'TEXT', nullable: true);

            $sql = $this->generator->generateAddColumn('users', $column);

            expect($sql)->toBe('ALTER TABLE "users" ADD COLUMN "bio" TEXT');
        });
    });

    describe('generateDropColumn', function (): void {
        it('generates ALTER TABLE DROP COLUMN', function (): void {
            $sql = $this->generator->generateDropColumn('users', 'bio');

            expect($sql)->toBe('ALTER TABLE "users" DROP COLUMN "bio"');
        });
    });

    describe('generateModifyColumn', function (): void {
        it('generates ALTER TABLE MODIFY COLUMN', function (): void {
            $oldColumn = new Column(name: 'name', type: 'VARCHAR', length: 100);
            $newColumn = new Column(name: 'name', type: 'VARCHAR', length: 255);

            $sql = $this->generator->generateModifyColumn('users', $newColumn, $oldColumn);

            expect($sql)->toContain('ALTER TABLE "users" MODIFY COLUMN "name" TEXT');
        });
    });

    describe('generateAddIndex', function (): void {
        it('generates CREATE INDEX statement', function (): void {
            $index = new Index(name: 'idx_name', columns: ['name']);

            $sql = $this->generator->generateAddIndex('users', $index);

            expect($sql)->toBe('CREATE INDEX "idx_name" ON "users" ("name")');
        });

        it('generates UNIQUE INDEX for unique indexes', function (): void {
            $index = new Index(name: 'idx_email', columns: ['email'], type: IndexType::Unique);

            $sql = $this->generator->generateAddIndex('users', $index);

            expect($sql)->toContain('UNIQUE INDEX');
        });
    });

    describe('generateDropIndex', function (): void {
        it('generates DROP INDEX statement', function (): void {
            $sql = $this->generator->generateDropIndex('users', 'idx_name');

            expect($sql)->toBe('DROP INDEX "idx_name"');
        });
    });

    describe('generateAddForeignKey', function (): void {
        it('generates ALTER TABLE ADD FOREIGN KEY', function (): void {
            $fk = new ForeignKey(
                name: 'fk_user',
                columns: ['user_id'],
                referencedTable: 'users',
                referencedColumns: ['id'],
            );

            $sql = $this->generator->generateAddForeignKey('posts', $fk);

            expect($sql)->toContain('ALTER TABLE "posts" ADD FOREIGN KEY ("user_id")');
            expect($sql)->toContain('REFERENCES "users"("id")');
        });
    });

    describe('generateDropForeignKey', function (): void {
        it('generates ALTER TABLE DROP FOREIGN KEY', function (): void {
            $sql = $this->generator->generateDropForeignKey('posts', 'fk_user');

            expect($sql)->toBe('ALTER TABLE "posts" DROP FOREIGN KEY "fk_user"');
        });
    });

    describe('generateUp', function (): void {
        it('generates CREATE TABLE statements', function (): void {
            $table = new Table(
                name: 'users',
                columns: [
                    new Column(name: 'id', type: 'INT', primaryKey: true),
                ],
            );
            $diff = new SchemaDiff(tablesToCreate: [$table]);

            $sql = $this->generator->generateUp($diff);

            expect($sql)->toHaveCount(1);
            expect($sql[0])->toContain('CREATE TABLE "users"');
        });
    });
});
