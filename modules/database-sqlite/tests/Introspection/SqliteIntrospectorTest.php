<?php

declare(strict_types=1);

namespace Marko\Sqlite\Tests\Introspection;

use Marko\Sqlite\Connection\SqliteConnection;
use Marko\Sqlite\Introspection\SqliteIntrospector;

describe('SqliteIntrospector', function (): void {
    beforeEach(function (): void {
        $this->connection = new SqliteConnection(':memory:');
        $this->connection->connect();
        $this->introspector = new SqliteIntrospector($this->connection);
    });

    it('returns empty array when no tables exist', function (): void {
        expect($this->introspector->getTables())->toBe([]);
    });

    it('discovers created tables', function (): void {
        $this->connection->execute('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT)');
        
        expect($this->introspector->getTables())->toBe(['users']);
    });

    it('detects table existence', function (): void {
        expect($this->introspector->tableExists('users'))->toBe(false);
        
        $this->connection->execute('CREATE TABLE users (id INTEGER)');
        
        expect($this->introspector->tableExists('users'))->toBe(true);
    });

    it('returns table metadata', function (): void {
        $this->connection->execute('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT NOT NULL)');
        
        $table = $this->introspector->getTable('users');
        
        expect($table)->not->toBeNull();
        expect($table?->name)->toBe('users');
        expect($table?->columns)->toHaveCount(2);
    });

    it('detects primary key column', function (): void {
        $this->connection->execute('CREATE TABLE posts (id INTEGER PRIMARY KEY, title TEXT)');
        
        $columns = $this->introspector->getColumns('posts');
        
        expect($columns[0]->name)->toBe('id');
        expect($columns[0]->primaryKey)->toBe(true);
    });

    it('detects nullable columns', function (): void {
        $this->connection->execute('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT NOT NULL, email TEXT)');
        
        $columns = $this->introspector->getColumns('users');
        
        expect($columns[1]->name)->toBe('name');
        expect($columns[1]->nullable)->toBe(false);
        expect($columns[2]->name)->toBe('email');
        expect($columns[2]->nullable)->toBe(true);
    });

    it('detects indexes', function (): void {
        $this->connection->execute('CREATE TABLE users (id INTEGER PRIMARY KEY, email TEXT)');
        $this->connection->execute('CREATE INDEX idx_email ON users(email)');
        
        $indexes = $this->introspector->getIndexes('users');
        
        expect($indexes)->toHaveCount(1);
        expect($indexes[0]->name)->toBe('idx_email');
        expect($indexes[0]->columns)->toBe(['email']);
    });

    it('detects foreign keys', function (): void {
        $this->connection->execute('CREATE TABLE posts (id INTEGER PRIMARY KEY, user_id INTEGER REFERENCES users(id))');
        
        $foreignKeys = $this->introspector->getForeignKeys('posts');
        
        expect($foreignKeys)->toHaveCount(1);
        expect($foreignKeys[0]->columns[0])->toBe('user_id');
        expect($foreignKeys[0]->referencedTable)->toBe('users');
    });

    it('returns primary key columns', function (): void {
        $this->connection->execute('CREATE TABLE users (id INTEGER PRIMARY KEY, email TEXT)');
        
        $pk = $this->introspector->getPrimaryKey('users');
        
        expect($pk)->toBe(['id']);
    });

    it('returns null for non-existent table', function (): void {
        expect($this->introspector->getTable('nonexistent'))->toBeNull();
    });
});
