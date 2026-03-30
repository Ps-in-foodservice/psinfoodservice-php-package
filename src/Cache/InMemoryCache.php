<?php

declare(strict_types=1);
namespace PSinfoodservice\Cache;

use PSinfoodservice\Contracts\CacheInterface;

/**
 * Simple in-memory cache implementation.
 *
 * This cache stores data in memory for the duration of the request.
 * Data is lost when the script terminates.
 *
 * Useful for caching master data during a single request to avoid
 * redundant API calls.
 *
 * @example
 * ```php
 * $cache = new InMemoryCache();
 * $cache->set('masters', $masterData, 3600); // Cache for 1 hour
 *
 * if ($cache->has('masters')) {
 *     $data = $cache->get('masters');
 * }
 * ```
 */
class InMemoryCache implements CacheInterface
{
    /**
     * Cache storage.
     *
     * @var array<string, array{value: mixed, expires: int|null}>
     */
    private array $cache = [];

    /**
     * {@inheritdoc}
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (!$this->has($key)) {
            return $default;
        }

        return $this->cache[$key]['value'];
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        $expires = $ttl !== null ? time() + $ttl : null;

        $this->cache[$key] = [
            'value' => $value,
            'expires' => $expires
        ];

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $key): bool
    {
        unset($this->cache[$key]);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): bool
    {
        $this->cache = [];
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $key): bool
    {
        if (!isset($this->cache[$key])) {
            return false;
        }

        $item = $this->cache[$key];

        // Check if expired
        if ($item['expires'] !== null && $item['expires'] < time()) {
            $this->delete($key);
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function setMultiple(iterable $values, ?int $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMultiple(iterable $keys): bool
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }
        return true;
    }

    /**
     * Get the number of items currently in cache.
     *
     * @return int
     */
    public function count(): int
    {
        // Clean expired items first
        foreach (array_keys($this->cache) as $key) {
            $this->has($key); // This will delete expired items
        }
        return count($this->cache);
    }

    /**
     * Get all cache keys.
     *
     * @return array<string>
     */
    public function keys(): array
    {
        // Clean expired items first
        foreach (array_keys($this->cache) as $key) {
            $this->has($key);
        }
        return array_keys($this->cache);
    }
}
