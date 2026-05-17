<?php

declare(strict_types=1);

namespace App\DatabaseModular;

use App\AppLogger;
use Exception;

class FileCache
{
    private string $cacheDir;
    private int $ttl;
    private bool $enabled;

    /**
     * @param string $cacheDir Directory where cache files will be stored
     * @param int $ttl Default time-to-live in seconds
     * @param bool $enabled Whether caching is enabled
     */
    public function __construct(string $cacheDir, int $ttl = 300, bool $enabled = true)
    {
        $this->cacheDir = rtrim($cacheDir, DIRECTORY_SEPARATOR);
        $this->ttl = $ttl;
        $this->enabled = $enabled;
        
        $this->ensureCacheDirectory();
    }

    /**
     * Get a cached value by key
     *
     * @param string $key Cache key
     * @return mixed|null Cached value or null if not found/expired
     */
    public function get(string $key): mixed
    {
        if (!$this->enabled) {
            return null;
        }

        try {
            $filePath = $this->getCacheFilePath($key);
            
            if (!file_exists($filePath)) {
                return null;
            }

            if ($this->isExpired($filePath)) {
                $this->delete($key);
                return null;
            }

            $content = file_get_contents($filePath);
            if ($content === false) {
                return null;
            }

            // Extract only the data part after the pipe character
            $pipePos = strpos($content, '|');
            if ($pipePos === false) {
                return null;
            }

            $data = substr($content, $pipePos + 1);
            $unserialized = unserialize($data);
            return $unserialized !== false ? $unserialized : null;
            
        } catch (Exception $e) {
            AppLogger::error("[FileCache] Error reading cache for key '{$key}': " . $e->getMessage());
            return null;
        }
    }

    /**
     * Store a value in cache
     *
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int|null $ttl Optional TTL override in seconds
     * @return bool Success status
     */
    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        if (!$this->enabled) {
            return false;
        }

        try {
            $filePath = $this->getCacheFilePath($key);
            $ttl = $ttl ?? $this->ttl;
            
            $data = serialize($value);
            $expiration = time() + $ttl;
            
            $content = $expiration . '|' . $data;
            
            $result = file_put_contents($filePath, $content, LOCK_EX) !== false;
            
            if (!$result) {
                AppLogger::error("[FileCache] Failed to write cache file for key '{$key}'");
                return false;
            }

            return true;
            
        } catch (Exception $e) {
            AppLogger::error("[FileCache] Error writing cache for key '{$key}': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a cache entry
     *
     * @param string $key Cache key
     * @return bool Success status
     */
    public function delete(string $key): bool
    {
        try {
            $filePath = $this->getCacheFilePath($key);
            
            if (file_exists($filePath)) {
                return unlink($filePath);
            }
            
            return true;
            
        } catch (Exception $e) {
            AppLogger::error("[FileCache] Error deleting cache for key '{$key}': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear all cache entries
     *
     * @return bool Success status
     */
    public function clear(): bool
    {
        try {
            if (!is_dir($this->cacheDir)) {
                return true;
            }

            $files = glob($this->cacheDir . '/*');
            if (!is_array($files)) {
                return false;
            }

            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }

            return true;
            
        } catch (Exception $e) {
            AppLogger::error("[FileCache] Error clearing cache: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if a cache entry exists and is not expired
     *
     * @param string $key Cache key
     * @return bool
     */
    public function has(string $key): bool
    {
        if (!$this->enabled) {
            return false;
        }

        try {
            $filePath = $this->getCacheFilePath($key);
            
            if (!file_exists($filePath)) {
                return false;
            }

            return !$this->isExpired($filePath);
            
        } catch (Exception $e) {
            AppLogger::error("[FileCache] Error checking cache for key '{$key}': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get the filesystem path for a cache key
     */
    private function getCacheFilePath(string $key): string
    {
        // Sanitize key for filesystem
        $safeKey = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $key);
        return $this->cacheDir . DIRECTORY_SEPARATOR . $safeKey . '.cache';
    }

    /**
     * Check if a cache file is expired
     */
    private function isExpired(string $filePath): bool
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return true;
        }

        $pipePos = strpos($content, '|');
        if ($pipePos === false) {
            return true;
        }

        $expiration = (int)substr($content, 0, $pipePos);
        return time() > $expiration;
    }

    /**
     * Ensure cache directory exists and is writable
     */
    private function ensureCacheDirectory(): void
    {
        if (!is_dir($this->cacheDir)) {
            if (!mkdir($concurrentDirectory = $this->cacheDir, 0755, true) && !is_dir($this->cacheDir)) {
                AppLogger::error("[FileCache] Failed to create cache directory: {$this->cacheDir}");
                $this->enabled = false;
            }
        }

        if (!is_writable($this->cacheDir)) {
            AppLogger::error("[FileCache] Cache directory is not writable: {$this->cacheDir}");
            $this->enabled = false;
        }
    }

    /**
     * Enable caching
     */
    public function enable(): void
    {
        $this->enabled = true;
    }

    /**
     * Disable caching
     */
    public function disable(): void
    {
        $this->enabled = false;
    }

    /**
     * Check if caching is enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}