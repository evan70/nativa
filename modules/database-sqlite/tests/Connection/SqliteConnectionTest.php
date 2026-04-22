<?php

declare(strict_types=1);

namespace Marko\Sqlite\Tests\Connection;

use Marko\Sqlite\Connection\SqliteConnection;
use Marko\Sqlite\Connection\SqliteException;

describe('SqliteConnection', function (): void {
    beforeEach(function (): void {
        $this->dbPath = ':memory:';
        $this->connection = new SqliteConnection($this->dbPath);
    });

    it('connects to in-memory database', function (): void {
        $this->connection->connect();
        expect($this->connection->isConnected())->toBe(true);
    });

    it('executes query and returns results', function (): void {
        $this->connection->connect();
        $this->connection->execute('CREATE TABLE test (id INTEGER, name TEXT)');
        
        $results = $this->connection->query('SELECT * FROM test');
        expect($results)->toBe([]);
    });

    it('executes query with bindings', function (): void {
        $this->connection->connect();
        $this->connection->execute('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT)');
        $this->connection->execute('INSERT INTO users (name) VALUES (?)', ['Alice']);
        
        $results = $this->connection->query('SELECT * FROM users');
        
        expect($results)->toHaveCount(1);
        expect($results[0]['name'])->toBe('Alice');
    });

    it('returns last insert id', function (): void {
        $this->connection->connect();
        $this->connection->execute('CREATE TABLE items (id INTEGER PRIMARY KEY, name TEXT)');
        $this->connection->execute('INSERT INTO items (name) VALUES (?)', ['Test']);
        
        expect($this->connection->lastInsertId())->toBe('1');
    });

    it('throws SqliteException on invalid query', function (): void {
        $this->connection->connect();
        
        expect(fn () => $this->connection->execute('INVALID SQL'))
            ->toThrow(SqliteException::class);
    });

    it('handles multiple statements', function (): void {
        $this->connection->connect();
        
        $this->connection->execute('
            CREATE TABLE posts (id INTEGER PRIMARY KEY, title TEXT);
            INSERT INTO posts (title) VALUES ("Hello");
        ');
        
        $results = $this->connection->query('SELECT * FROM posts');
        
        expect($results)->toHaveCount(1);
        expect($results[0]['title'])->toBe('Hello');
    });
});