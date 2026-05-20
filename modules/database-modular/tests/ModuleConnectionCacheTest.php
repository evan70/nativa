<?php

declare(strict_types=1);

namespace App\DatabaseModular\Tests;

use App\DatabaseModular\ModuleConnection;
use PHPUnit\Framework\TestCase;

class ModuleConnectionCacheTest extends TestCase
{
    private string $testDbPath;
    
    protected function setUp(): void
    {
        $this->testDbPath = sys_get_temp_dir() . '/test_cache_db_' . uniqid() . '.db';
        touch($this->testDbPath);
    }
    
    protected function tearDown(): void
    {
        if (file_exists($this->testDbPath)) {
            unlink($this->testDbPath);
        }
    }
    
    public function testFileCacheFallbackWhenMemcachedUnavailable(): void
    {
        // Set environment to use file cache
        putenv('CACHE_DRIVER=file');
        putenv('CACHE_FILE_PATH=' . sys_get_temp_dir() . '/cache_test_' . uniqid());
        putenv('CACHE_ENABLED=true');
        
        $pdo = new \PDO('sqlite:' . $this->testDbPath);
        $connection = new ModuleConnection($pdo);
        
        // Test that file cache is initialized
        $reflection = new \ReflectionClass($connection);
        $cacheDriverProperty = $reflection->getProperty('cacheDriver');
        $cacheDriver = $cacheDriverProperty->getValue();
        
        $this->assertEquals('file', $cacheDriver);
        
        // Test basic query caching
        $pdo->exec('CREATE TABLE test (id INTEGER PRIMARY KEY, name TEXT)');
        $pdo->exec('INSERT INTO test (name) VALUES ("test1")');
        
        // First query - should cache
        $result1 = $connection->query('SELECT * FROM test');
        $this->assertCount(1, $result1);
        
        // Second query - should come from cache
        $result2 = $connection->query('SELECT * FROM test');
        $this->assertEquals($result1, $result2);
    }
    
    public function testMemcachedFallbackToFileCache(): void
    {
        // Set environment to use memcached (which won't be available in test)
        putenv('CACHE_DRIVER=memcached');
        putenv('CACHE_FILE_PATH=' . sys_get_temp_dir() . '/cache_test_' . uniqid());
        putenv('CACHE_ENABLED=true');
        
        $pdo = new \PDO('sqlite:' . $this->testDbPath);
        $connection = new ModuleConnection($pdo);
        
        // Since Memcached extension is not available in test environment,
        // it should fall back to file cache
        $reflection = new \ReflectionClass($connection);
        $cacheDriverProperty = $reflection->getProperty('cacheDriver');
        $cacheDriver = $cacheDriverProperty->getValue();
        
        $this->assertEquals('file', $cacheDriver);
    }
    
    public function testCacheInvalidationOnWriteOperations(): void
    {
        putenv('CACHE_DRIVER=file');
        putenv('CACHE_FILE_PATH=' . sys_get_temp_dir() . '/cache_test_' . uniqid());
        putenv('CACHE_ENABLED=true');
        
        $pdo = new \PDO('sqlite:' . $this->testDbPath);
        $connection = new ModuleConnection($pdo);
        
        $pdo->exec('CREATE TABLE test (id INTEGER PRIMARY KEY, name TEXT)');
        
        // Insert data
        $connection->execute('INSERT INTO test (name) VALUES ("test1")');
        
        // Query data
        $result1 = $connection->query('SELECT * FROM test');
        $this->assertCount(1, $result1);
        
        // Update data (should invalidate cache)
        $connection->execute('UPDATE test SET name = "test2" WHERE id = 1');
        
        // Query again - should get fresh data, not cached
        $result2 = $connection->query('SELECT * FROM test');
        $this->assertCount(1, $result2);
        $this->assertEquals('test2', $result2[0]['name']);
    }
}