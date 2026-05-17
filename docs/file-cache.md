# File Cache Implementation

## Overview

The file cache system provides a file-based caching alternative to Memcached, with automatic fallback when Memcached is unavailable. This implementation is designed to work seamlessly with the existing database connection caching system.

## Configuration

### Environment Variables

Add these to your `.env` file:

```env
# Cache configuration
CACHE_ENABLED=true
CACHE_TTL=300  # seconds (5 minutes default)
CACHE_DRIVER=memcached  # memcached or file
CACHE_FILE_PATH=storage/cache
```

### Cache Drivers

- **memcached** (default): Uses Memcached for distributed caching
- **file**: Uses file-based caching with automatic fallback

### Fallback Behavior

The system automatically falls back from Memcached to file cache in these scenarios:

1. **Memcached extension not installed**: If the PHP Memcached extension is missing
2. **Memcached connection failure**: If Memcached server is unavailable
3. **Explicit file driver selection**: When `CACHE_DRIVER=file` is set

## Usage

### Basic Usage

The file cache is automatically used by the `ModuleConnection` class when appropriate. No code changes are needed for basic usage.

### Manual Cache Operations

For advanced usage, you can access the cache directly:

```php
use App\DatabaseModular\FileCache;

// Initialize file cache
$cache = new FileCache(
    $cacheDir = 'storage/cache',
    $ttl = 300,  // 5 minutes
    $enabled = true
);

// Store data
$cache->set('user_data_123', $userData);

// Retrieve data
$userData = $cache->get('user_data_123');

// Check if cached
if ($cache->has('user_data_123')) {
    // Data is cached and not expired
}

// Delete specific cache entry
$cache->delete('user_data_123');

// Clear all cache
$cache->clear();

// Disable caching temporarily
$cache->disable();
// ... perform operations without caching ...
$cache->enable();
```

## File Cache Features

### Key Features

- **Automatic serialization**: Handles complex data types automatically
- **TTL support**: Time-to-live expiration for cache entries
- **Key sanitization**: Safe filesystem-friendly cache keys
- **Error handling**: Graceful degradation on filesystem errors
- **Atomic operations**: File locking for concurrent access

### Cache Key Format

Cache keys are automatically sanitized for filesystem compatibility:
- Special characters are replaced with underscores
- Keys are stored as files with `.cache` extension
- Example: `user_profile_123` → `user_profile_123.cache`

### Cache File Format

Each cache file contains:
```
<expiration_timestamp>|<serialized_data>
```

## Performance Considerations

### When to Use File Cache

- **Development environments** where Memcached is not available
- **Small to medium applications** with moderate cache needs
- **Fallback scenarios** when Memcached fails
- **Simple deployments** without distributed caching needs

### When to Use Memcached

- **Production environments** with high traffic
- **Distributed applications** needing shared cache
- **High-performance requirements** with frequent cache access
- **Large-scale applications** with significant cache data

## Testing

Run the test suite to verify file cache functionality:

```bash
# Run file cache tests
./vendor/bin/phpunit modules/database-modular/tests/FileCacheTest.php

# Run integration tests
./vendor/bin/phpunit modules/database-modular/tests/ModuleConnectionCacheTest.php
```

## Troubleshooting

### Common Issues

**Cache directory not writable**:
- Ensure the `CACHE_FILE_PATH` directory exists and is writable by the web server
- Check permissions: `chmod 755 storage/cache`

**Cache not working**:
- Verify `CACHE_ENABLED=true` in environment
- Check logs for cache initialization errors
- Test with simple cache operations first

**Performance issues**:
- File cache is slower than Memcached for high-frequency operations
- Consider reducing cache TTL or using Memcached in production

## Migration Guide

### From No Caching to File Cache

1. Add environment variables to `.env`
2. Ensure cache directory exists: `mkdir -p storage/cache`
3. Set permissions: `chmod 755 storage/cache`
4. Test with `CACHE_DRIVER=file`

### From Memcached to File Cache

1. Change `CACHE_DRIVER=memcached` to `CACHE_DRIVER=file`
2. Ensure cache directory exists and is writable
3. Monitor performance and adjust as needed

## Advanced Configuration

### Custom Cache Directory

```env
CACHE_FILE_PATH=/custom/path/to/cache
```

### Different TTL per Operation

```php
// Use default TTL (from CACHE_TTL)
$cache->set('key1', $data);

// Use custom TTL (60 seconds)
$cache->set('key2', $data, 60);
```

### Cache Disabling

```php
// Disable caching entirely
CACHE_ENABLED=false

// Or disable programmatically
$cache->disable();
```

## Security Considerations

- Cache directory should not be web-accessible
- Cache files contain serialized data - ensure proper access controls
- Sensitive data should not be cached without encryption
- Regular cache clearing recommended for security-sensitive applications

## Monitoring and Maintenance

### Cache Statistics

```php
// Get cache directory size
$size = $this->getDirectorySize($cacheDir);

// Count cache files
$files = count(glob($cacheDir . '/*.cache'));
```

### Cache Cleanup

```bash
# Manual cleanup
rm -f storage/cache/*.cache

# Or programmatically
$cache->clear();
```

## Integration with Existing Code

The file cache system is fully integrated with the `ModuleConnection` class:

- **SELECT queries**: Automatically cached with TTL
- **INSERT/UPDATE/DELETE**: Automatically invalidate relevant cache
- **Cache keys**: Generated from SQL + parameters
- **Fallback**: Automatic between Memcached and file cache

No changes to existing query code are required.