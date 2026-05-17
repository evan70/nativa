<?php

declare(strict_types=1);

namespace App\DatabaseModular\Tests;

use App\DatabaseModular\FileCache;
use PHPUnit\Framework\TestCase;

class FileCacheTest extends TestCase
{
    private string $testCacheDir;
    private FileCache $fileCache;

    protected function setUp(): void
    {
        $this->testCacheDir = sys_get_temp_dir() . '/filecache_test_' . uniqid();
        $this->fileCache = new FileCache($this->testCacheDir, 300, true);
    }

    protected function tearDown(): void
    {
        // Clean up test directory
        if (is_dir($this->testCacheDir)) {
            $files = glob($this->testCacheDir . '/*');
            if (is_array($files)) {
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }
            rmdir($this->testCacheDir);
        }
    }

    public function testFileCacheInitialization(): void
    {
        $this->assertTrue($this->fileCache->isEnabled());
        $this->assertDirectoryExists($this->testCacheDir);
    }

    public function testFileCacheSetAndGet(): void
    {
        $key = 'test_key';
        $value = ['data' => 'test', 'number' => 42];

        $result = $this->fileCache->set($key, $value);
        $this->assertTrue($result);

        $retrieved = $this->fileCache->get($key);
        $this->assertEquals($value, $retrieved);
    }

    public function testFileCacheGetNonExistentKey(): void
    {
        $result = $this->fileCache->get('non_existent_key');
        $this->assertNull($result);
    }

    public function testFileCacheHas(): void
    {
        $key = 'test_has_key';
        $value = ['test' => 'has'];

        $this->assertFalse($this->fileCache->has($key));

        $this->fileCache->set($key, $value);
        $this->assertTrue($this->fileCache->has($key));
    }

    public function testFileCacheDelete(): void
    {
        $key = 'test_delete_key';
        $value = ['test' => 'delete'];

        $this->fileCache->set($key, $value);
        $this->assertTrue($this->fileCache->has($key));

        $result = $this->fileCache->delete($key);
        $this->assertTrue($result);
        $this->assertFalse($this->fileCache->has($key));
    }

    public function testFileCacheClear(): void
    {
        $this->fileCache->set('key1', ['data' => '1']);
        $this->fileCache->set('key2', ['data' => '2']);
        $this->fileCache->set('key3', ['data' => '3']);

        $this->assertTrue($this->fileCache->has('key1'));
        $this->assertTrue($this->fileCache->has('key2'));
        $this->assertTrue($this->fileCache->has('key3'));

        $result = $this->fileCache->clear();
        $this->assertTrue($result);

        $this->assertFalse($this->fileCache->has('key1'));
        $this->assertFalse($this->fileCache->has('key2'));
        $this->assertFalse($this->fileCache->has('key3'));
    }

    public function testFileCacheExpiration(): void
    {
        $key = 'test_expiration';
        $value = ['test' => 'expiration'];

        // Set with 1 second TTL
        $this->fileCache->set($key, $value, 1);

        $this->assertTrue($this->fileCache->has($key));
        $this->assertEquals($value, $this->fileCache->get($key));

        // Wait for expiration
        sleep(2);

        $this->assertFalse($this->fileCache->has($key));
        $this->assertNull($this->fileCache->get($key));
    }

    public function testFileCacheDisabled(): void
    {
        $disabledCache = new FileCache($this->testCacheDir, 300, false);
        
        $this->assertFalse($disabledCache->isEnabled());
        
        $result = $disabledCache->set('test_key', ['test' => 'data']);
        $this->assertFalse($result);
        
        $retrieved = $disabledCache->get('test_key');
        $this->assertNull($retrieved);
    }

    public function testFileCacheKeySanitization(): void
    {
        $specialKey = 'test/key:with@special!chars';
        $value = ['test' => 'special'];

        $result = $this->fileCache->set($specialKey, $value);
        $this->assertTrue($result);

        $retrieved = $this->fileCache->get($specialKey);
        $this->assertEquals($value, $retrieved);

        // Check that the file was created with sanitized name
        $files = glob($this->testCacheDir . '/*');
        $this->assertCount(1, $files);
        $this->assertStringContainsString('test_key_with_special_chars', $files[0]);
    }

    public function testFileCacheComplexData(): void
    {
        $complexData = [
            'string' => 'test string',
            'number' => 42,
            'float' => 3.14,
            'bool' => true,
            'null' => null,
            'array' => ['nested' => 'data'],
            'object' => (object)['key' => 'value']
        ];

        $key = 'complex_data_test';
        $result = $this->fileCache->set($key, $complexData);
        $this->assertTrue($result);

        $retrieved = $this->fileCache->get($key);
        $this->assertEquals($complexData, $retrieved);
    }

    public function testFileCacheDirectoryNotWritable(): void
    {
        // Create a directory that's not writable
        $nonWritableDir = sys_get_temp_dir() . '/nonwritable_' . uniqid();
        mkdir($nonWritableDir);
        chmod($nonWritableDir, 0444); // Read-only

        $cache = new FileCache($nonWritableDir, 300, true);
        $this->assertFalse($cache->isEnabled());

        // Clean up
        chmod($nonWritableDir, 0755);
        rmdir($nonWritableDir);
    }

    public function testFileCacheEnableDisable(): void
    {
        $key = 'enable_disable_test';
        $value = ['test' => 'enable_disable'];

        $this->fileCache->set($key, $value);
        $this->assertEquals($value, $this->fileCache->get($key));

        $this->fileCache->disable();
        $this->assertFalse($this->fileCache->isEnabled());
        $this->assertNull($this->fileCache->get($key));

        $this->fileCache->enable();
        $this->assertTrue($this->fileCache->isEnabled());
        $this->assertEquals($value, $this->fileCache->get($key));
    }
}