<?php

declare(strict_types=1);

namespace App\DatabaseModular;

use App\AppLogger;
use Marko\Database\Connection\ConnectionInterface;
use Memcached;
use PDO;
use Exception;

class ModuleConnection implements ConnectionInterface
{
    private ?PDO $pdo = null;
    private static ?Memcached $sharedMemcached = null;  // Shared across all instances
    private static ?FileCache $sharedFileCache = null;  // Shared file cache instance
    private static bool $cacheEnabled = true;
    private static int $cacheTtl = 300;  // Shared TTL
    private static bool $initialized = false;
    private static string $cacheDriver = 'memcached';  // Default cache driver
    
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->initCache();
    }

    /**
     * Initialize cache system (static - only once)
     */
    private function initCache(): void
    {
        // Already initialized - use shared instance
        if (self::$initialized) {
            return;
        }

        // Get cache driver from environment
        $cacheDriver = \env('CACHE_DRIVER', 'memcached');
        self::$cacheDriver = is_string($cacheDriver) ? $cacheDriver : 'memcached';

        // Check if caching is enabled via environment variable
        $cacheEnabled = \env('CACHE_ENABLED', true);
        if (!$cacheEnabled) {
            AppLogger::info('[ModuleConnection] Caching disabled via CACHE_ENABLED env');
            self::$cacheEnabled = false;
            self::$initialized = true;
            return;
        }

        // Get TTL from environment (default 5 minutes)
        $envTtl = \env('CACHE_TTL', 300);
        self::$cacheTtl = is_numeric($envTtl) ? (int) $envTtl : 300;

        // Initialize based on driver
        if ($cacheDriver === 'file') {
            $this->initFileCache();
        } else {
            $this->initMemcached();
        }

        self::$initialized = true;
    }

    /**
     * Initialize memcached connection
     */
    private function initMemcached(): void
    {
        // Check if Memcached extension is available
        if (!class_exists('Memcached')) {
            AppLogger::info('[ModuleConnection] Memcached extension not installed - falling back to file cache');
            $this->initFileCache();
            return;
        }

        $hostEnv = \env('MEMCACHED_HOST', 'localhost');
        $host = is_string($hostEnv) ? $hostEnv : 'localhost';
        $portEnv = \env('MEMCACHED_PORT', 11211);
        $port = is_numeric($portEnv) ? (int) $portEnv : 11211;
        $prefixEnv = \env('MEMCACHED_PREFIX', 'nativa_db_');
        $prefix = is_string($prefixEnv) ? $prefixEnv : 'nativa_db_';

        try {
            self::$sharedMemcached = new Memcached();
            self::$sharedMemcached->addServer($host, $port);
            self::$sharedMemcached->setOption(Memcached::OPT_BINARY_PROTOCOL, true);
            
            // Set prefix for namespace isolation
            self::$sharedMemcached->setOption(Memcached::OPT_PREFIX_KEY, $prefix);

            AppLogger::info('[ModuleConnection] Memcached connected to ' . $host . ':' . $port . ' (TTL: ' . self::$cacheTtl . 's)');
        } catch (\Exception $e) {
            AppLogger::error('[ModuleConnection] Memcached connection failed: ' . $e->getMessage() . ' - falling back to file cache');
            $this->initFileCache();
        }
    }

    /**
     * Initialize file cache
     */
    private function initFileCache(): void
    {
        $cacheDirEnv = \env('CACHE_FILE_PATH', 'storage/cache');
        $cacheDir = is_string($cacheDirEnv) ? $cacheDirEnv : 'storage/cache';
        
        // If it's a relative path, make it absolute relative to project root
        if (!str_starts_with($cacheDir, '/') && !preg_match('/^[a-zA-Z]:\\\\/', $cacheDir)) {
            $cacheDir = dirname(__DIR__, 3) . '/' . ltrim($cacheDir, './');
        }
        
        try {
            self::$sharedFileCache = new FileCache($cacheDir, self::$cacheTtl, self::$cacheEnabled);
            self::$cacheDriver = 'file';
            
            if (self::$sharedFileCache->isEnabled()) {
                AppLogger::info('[ModuleConnection] File cache initialized (TTL: ' . self::$cacheTtl . 's, dir: ' . $cacheDir . ')');
            } else {
                AppLogger::error('[ModuleConnection] File cache disabled - directory not writable');
                self::$cacheEnabled = false;
            }
        } catch (\Exception $e) {
            AppLogger::error('[ModuleConnection] File cache initialization failed: ' . $e->getMessage() . ' - caching disabled');
            self::$cacheEnabled = false;
        }
    }

    /**
     * Generate cache key from SQL and params
     * 
     * @param array<mixed> $params
     */
    private function getCacheKey(string $sql, array $params): string
    {
        return md5($sql . ':' . serialize($params));
    }

    /**
     * Get cached result if available
     * 
     * @return array<mixed>|null
     */
    private function getCached(string $key): ?array
    {
        if (!self::$cacheEnabled) {
            return null;
        }

        if (self::$cacheDriver === 'file' && self::$sharedFileCache) {
            $result = self::$sharedFileCache->get($key);
            if ($result !== null) {
                AppLogger::debug('[ModuleConnection] Cache HIT (file): ' . $key);
                return (array) $result;
            }
        } elseif (self::$sharedMemcached) {
            $result = self::$sharedMemcached->get($key);
            if (self::$sharedMemcached->getResultCode() === Memcached::RES_SUCCESS) {
                AppLogger::debug('[ModuleConnection] Cache HIT (memcached): ' . $key);
                return (array) $result;
            }
        }
        
        AppLogger::debug('[ModuleConnection] Cache MISS: ' . $key);
        return null;
    }

    /**
     * Store result in cache
     * 
     * @param array<mixed> $data
     */
    private function setCached(string $key, array $data, ?int $ttl = null): void
    {
        if (!self::$cacheEnabled) {
            return;
        }

        $ttl = $ttl ?? self::$cacheTtl;
        
        if (self::$cacheDriver === 'file' && self::$sharedFileCache) {
            self::$sharedFileCache->set($key, $data, $ttl);
            AppLogger::debug('[ModuleConnection] Cached (file): ' . $key . ' (TTL: ' . $ttl . 's)');
        } elseif (self::$sharedMemcached) {
            self::$sharedMemcached->set($key, $data, $ttl);
            AppLogger::debug('[ModuleConnection] Cached (memcached): ' . $key . ' (TTL: ' . $ttl . 's)');
        }
    }

    /**
     * Invalidate cache for a specific query
     * 
     * @param array<mixed> $params
     */
    public function invalidateCache(string $sql, array $params): void
    {
        $key = $this->getCacheKey($sql, $params);
        
        if (self::$cacheDriver === 'file' && self::$sharedFileCache) {
            self::$sharedFileCache->delete($key);
            AppLogger::debug('[ModuleConnection] Cache invalidated (file): ' . $key);
        } elseif (self::$sharedMemcached) {
            self::$sharedMemcached->delete($key);
            AppLogger::debug('[ModuleConnection] Cache invalidated (memcached): ' . $key);
        }
    }

    /**
     * Invalidate all cache (flush namespace)
     */
    public function invalidateAllCache(): void
    {
        if (self::$cacheDriver === 'file' && self::$sharedFileCache) {
            self::$sharedFileCache->clear();
            AppLogger::debug('[ModuleConnection] Cache cleared (file)');
        } elseif (self::$sharedMemcached) {
            // With prefix key, we can't easily flush - would need to iterate and delete
            // For now, just log that we should consider a version key approach
            AppLogger::warning('[ModuleConnection] Cache flush requested (consider using version key)');
        }
    }
    
    /**
     * Execute query and return results
     * 
     * @param array<mixed> $params
     * @return array<array<string, mixed>>
     */
    public function query(string $sql, array $params = []): array
    {
        if ($this->pdo === null) {
            return [];
        }

        $key = $this->getCacheKey($sql, $params);
        $cached = $this->getCached($key);
        if ($cached !== null) {
            /** @var array<array<string, mixed>> $cached */
            return $cached;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        
        $this->setCached($key, (array) $results);
        
        /** @var array<array<string, mixed>> $results */
        return (array) $results;
    }
    
    /**
     * @param array<mixed> $params
     */
    public function execute(string $sql, array $params = []): int
    {
        /** @var PDO $pdo */
        $pdo = $this->pdo;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rowCount = $stmt->rowCount();

        // Invalidate cache after INSERT/UPDATE/DELETE
        // This is a simple approach - invalidates all cache
        // For more granular control, you'd want table-specific invalidation
        $sqlUpper = strtoupper(trim($sql));
        if (str_starts_with($sqlUpper, 'INSERT') || 
            str_starts_with($sqlUpper, 'UPDATE') || 
            str_starts_with($sqlUpper, 'DELETE')) {
            $this->invalidateAllCache();
            AppLogger::debug('[ModuleConnection] Cache invalidated due to: ' . $sqlUpper);
        }

        return $rowCount;
    }
    
    public function lastInsertId(): int
    {
        /** @var PDO $pdo */
        $pdo = $this->pdo;
        return (int) $pdo->lastInsertId();
    }
    
    public function prepare(string $sql): \Marko\Database\Connection\StatementInterface
    {
        /** @var PDO $pdo */
        $pdo = $this->pdo;
        $stmt = $pdo->prepare($sql);
        return new ModuleStatement($stmt);
    }
    
    public function connect(): void
    {
        // SQLite connects automatically
    }
    
    public function disconnect(): void
    {
        $this->pdo = null;
    }
    
    public function isConnected(): bool
    {
        return $this->pdo !== null;
    }
}
