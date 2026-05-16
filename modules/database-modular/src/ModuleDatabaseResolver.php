<?php

declare(strict_types=1);

namespace App\DatabaseModular;

use App\AppLogger;
use App\DatabaseModular\Contracts\ModuleDatabaseResolverInterface;
use Marko\Database\Connection\ConnectionInterface;
use Marko\Database\Connection\StatementInterface;
use Memcached;
use PDO;
use PDOStatement;

class ModuleDatabaseResolver implements ModuleDatabaseResolverInterface
{
    /**
     * @var array<string, string> - module name => database name mapping
     */
    private array $moduleMapping;
    
    /**
     * @var string - path to storage/data directory
     */
    private string $storagePath;
    
    /**
     * @param array<string, string> $moduleMapping
     */
    public function __construct(
        array $moduleMapping = [],
        ?string $storagePath = null
    ) {
        $this->moduleMapping = $moduleMapping;
        $this->storagePath = $storagePath ?? dirname(__DIR__, 3) . '/storage/data';
    }
    
    public function getDatabasePath(string $moduleName): string
    {
        if (!isset($this->moduleMapping[$moduleName])) {
            // Fallback to module name as database name
            return $this->storagePath . '/' . $moduleName . '.db';
        }
        
        return $this->storagePath . '/' . $this->moduleMapping[$moduleName] . '.db';
    }
    
    public function hasOwnDatabase(string $moduleName): bool
    {
        return isset($this->moduleMapping[$moduleName]);
    }
    
    /**
     * @param mixed $container
     * @psalm-suppress MixedMethodCall
     */
    public function getConnection(string $moduleName, mixed $container): ConnectionInterface
    {
        // If module has its own database, use it
        if ($this->hasOwnDatabase($moduleName)) {
            return $this->createConnection($this->getDatabasePath($moduleName));
        }
        
        // Otherwise, use the default global connection
        // @phpstan-ignore-next-line
        return $container->get(ConnectionInterface::class);
    }
    
    public function getRegisteredModules(): array
    {
        return $this->moduleMapping;
    }
    
    /**
     * Create a PDO connection for the given database path
     */
    private function createConnection(string $dbPath): ConnectionInterface
    {
        // Create a simple PDO-based connection for the module's database
        // This is a simplified implementation - in production you'd want proper connection pooling
        
        $pdo = new PDO(
            'sqlite:' . $dbPath,
            null,
            null,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
        
        // Return a wrapper that implements ConnectionInterface
        return new ModuleConnection($pdo);
    }
}

/**
 * Simple wrapper for PDO to satisfy ConnectionInterface
 */
class ModuleConnection implements ConnectionInterface
{
    private ?PDO $pdo = null;
    private static ?Memcached $sharedMemcached = null;  // Shared across all instances
    private static bool $cacheEnabled = true;
    private static int $cacheTtl = 300;  // Shared TTL
    private static bool $initialized = false;
    
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->initMemcached();
    }

    /**
     * Initialize memcached connection (static - only once)
     */
    private function initMemcached(): void
    {
        // Already initialized - use shared instance
        if (self::$initialized) {
            $this->memcached = self::$sharedMemcached;
            return;
        }

        // Check if Memcached extension is available
        if (!class_exists('Memcached')) {
            AppLogger::info('[ModuleConnection] Memcached extension not installed - caching disabled');
            self::$cacheEnabled = false;
            return;
        }

        // Check if caching is enabled via environment variable
        $cacheEnabled = getenv('CACHE_ENABLED');
        if ($cacheEnabled !== false && strtolower($cacheEnabled) === 'false') {
            AppLogger::info('[ModuleConnection] Caching disabled via CACHE_ENABLED env');
            self::$cacheEnabled = false;
            return;
        }

        $host = getenv('MEMCACHED_HOST') ?: 'localhost';
        $port = (int)(getenv('MEMCACHED_PORT') ?: 11211);
        $prefix = getenv('MEMCACHED_PREFIX') ?: 'nativa_db_';

        // Get TTL from environment (default 5 minutes)
        $envTtl = getenv('CACHE_TTL');
        if ($envTtl !== false) {
            self::$cacheTtl = (int)$envTtl;
        }

        try {
            self::$sharedMemcached = new Memcached();
            self::$sharedMemcached->addServer($host, $port);
            self::$sharedMemcached->setOption(Memcached::OPT_BINARY_PROTOCOL, true);
            
            // Set prefix for namespace isolation
            self::$sharedMemcached->setOption(Memcached::OPT_PREFIX_KEY, $prefix);
            
            self::$initialized = true;
            $this->memcached = self::$sharedMemcached;
            AppLogger::info('[ModuleConnection] Memcached connected to ' . $host . ':' . $port . ' (TTL: ' . self::$cacheTtl . 's)');
        } catch (\Exception $e) {
            AppLogger::error('[ModuleConnection] Memcached connection failed: ' . $e->getMessage());
            self::$cacheEnabled = false;
        }
    }

    private function getMemcached(): ?Memcached
    {
        return self::$sharedMemcached;
    }

    private function isCacheEnabled(): bool
    {
        return self::$cacheEnabled;
    }

    private function getCacheTtl(): int
    {
        return self::$cacheTtl;
    }

    /**
     * Generate cache key from SQL and params
     */
    private function getCacheKey(string $sql, array $params): string
    {
        return md5($sql . ':' . serialize($params));
    }

    /**
     * Get cached result if available
     */
    private function getCached(string $key): ?array
    {
        if (!self::$cacheEnabled || !self::$sharedMemcached) {
            return null;
        }

        $result = self::$sharedMemcached->get($key);
        if (self::$sharedMemcached->getResultCode() === Memcached::RES_SUCCESS) {
            AppLogger::debug('[ModuleConnection] Cache HIT: ' . $key);
            return $result;
        }
        
        AppLogger::debug('[ModuleConnection] Cache MISS: ' . $key);
        return null;
    }

    /**
     * Store result in cache
     */
    private function setCached(string $key, array $data, ?int $ttl = null): void
    {
        if (!self::$cacheEnabled || !self::$sharedMemcached) {
            return;
        }

        $ttl = $ttl ?? self::$cacheTtl;
        self::$sharedMemcached->set($key, $data, $ttl);
        AppLogger::debug('[ModuleConnection] Cached: ' . $key . ' (TTL: ' . $ttl . 's)');
    }

    /**
     * Invalidate cache for a specific key
     */
    public function invalidateCache(string $sql, array $params = []): void
    {
        $key = $this->getCacheKey($sql, $params);
        if (self::$sharedMemcached) {
            self::$sharedMemcached->delete($key);
            AppLogger::debug('[ModuleConnection] Cache invalidated: ' . $key);
        }
    }

    /**
     * Invalidate all cache (flush namespace)
     */
    public function invalidateAllCache(): void
    {
        // With prefix key, we can't easily flush - would need to iterate and delete
        // For now, just log that we should consider a version key approach
        AppLogger::warning('[ModuleConnection] Cache flush requested (consider using version key)');
    }
    
    /**
     * @param array<string, mixed> $params
     */
    /**
     * @param array<string, mixed> $params
     * @return array<array<string, mixed>>
     */
    public function query(string $sql, array $params = []): array
    {
        // Try cache first for SELECT queries
        if (stripos(trim($sql), 'SELECT') === 0) {
            $cacheKey = $this->getCacheKey($sql, $params);
            $cached = $this->getCached($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        /** @var PDO $pdo */
        $pdo = $this->pdo;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        /** @var array<array<string, mixed>> $result */
        $result = $stmt->fetchAll();

        // Cache SELECT results
        if (stripos(trim($sql), 'SELECT') === 0) {
            $cacheKey = $this->getCacheKey($sql, $params);
            $this->setCached($cacheKey, $result);
        }

        return $result;
    }
    
    /**
     * @param array<string, mixed> $params
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

/**
 * Simple wrapper for PDOStatement to satisfy StatementInterface
 */
class ModuleStatement implements StatementInterface
{
    private PDOStatement $statement;
    
    public function __construct(PDOStatement $statement)
    {
        $this->statement = $statement;
    }
    
    /**
     * @param array<string, mixed> $bindings
     */
    public function execute(array $bindings = []): bool
    {
        return $this->statement->execute($bindings);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function fetch(): ?array
    {
        /** @var array<string, mixed>|false $result */
        $result = $this->statement->fetch();
        return $result !== false ? $result : null;
    }
    
    /**
     * @return array<array<string, mixed>>
     */
    public function fetchAll(): array
    {
        /** @var array<array<string, mixed>> $result */
        $result = $this->statement->fetchAll();
        return $result;
    }
    
    public function rowCount(): int
    {
        return $this->statement->rowCount();
    }
}