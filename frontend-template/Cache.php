<?php
/**
 * Simple File-Based Cache
 * Multi-Tenant Journal Management System
 *
 * Provides caching for API responses to reduce load on Central API.
 */

class Cache
{
    private string $path;
    private int $defaultTtl;

    public function __construct(string $path, int $defaultTtl = 3600)
    {
        $this->path = rtrim($path, '/');
        $this->defaultTtl = $defaultTtl;

        // Create cache directory if it doesn't exist
        if (!is_dir($this->path)) {
            mkdir($this->path, 0755, true);
        }
    }

    /**
     * Get cache file path for a key
     */
    private function getFilePath(string $key): string
    {
        $safeKey = preg_replace('/[^a-zA-Z0-9_-]/', '_', $key);
        return $this->path . '/' . $safeKey . '.cache';
    }

    /**
     * Get cached value
     */
    public function get(string $key)
    {
        $file = $this->getFilePath($key);

        if (!file_exists($file)) {
            return null;
        }

        $content = file_get_contents($file);
        $data = unserialize($content);

        // Check expiration
        if ($data['expires_at'] < time()) {
            unlink($file);
            return null;
        }

        return $data['value'];
    }

    /**
     * Set cached value
     */
    public function set(string $key, $value, ?int $ttl = null): void
    {
        $file = $this->getFilePath($key);
        $ttl = $ttl ?? $this->defaultTtl;

        $data = [
            'value' => $value,
            'expires_at' => time() + $ttl
        ];

        file_put_contents($file, serialize($data), LOCK_EX);
    }

    /**
     * Delete cached value
     */
    public function delete(string $key): void
    {
        $file = $this->getFilePath($key);
        if (file_exists($file)) {
            unlink($file);
        }
    }

    /**
     * Clear all cache
     */
    public function clear(): void
    {
        $files = glob($this->path . '/*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
    }

    /**
     * Get or set value (cache-aside pattern)
     */
    public function remember(string $key, callable $callback, ?int $ttl = null)
    {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->set($key, $value, $ttl);

        return $value;
    }

    /**
     * Check if key exists and is not expired
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }
}
