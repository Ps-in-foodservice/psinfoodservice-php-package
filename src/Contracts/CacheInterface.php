<?php

declare(strict_types=1);
namespace PSinfoodservice\Contracts;

/**
 * Simple cache interface compatible with PSR-16 SimpleCache.
 *
 * This interface defines a basic caching contract that can be implemented
 * by various cache backends (in-memory, file, Redis, etc.).
 */
interface CacheInterface
{
    /**
     * Fetches a value from the cache.
     *
     * @param string $key The unique key of this item in the cache
     * @param mixed $default Default value to return if the key does not exist
     * @return mixed The value of the item from the cache, or $default if not found
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Persists data in the cache.
     *
     * @param string $key The key of the item to store
     * @param mixed $value The value of the item to store (must be serializable)
     * @param int|null $ttl Optional TTL in seconds. Null means cache forever.
     * @return bool True on success, false on failure
     */
    public function set(string $key, mixed $value, ?int $ttl = null): bool;

    /**
     * Delete an item from the cache.
     *
     * @param string $key The unique cache key
     * @return bool True if the item was successfully removed, false on failure
     */
    public function delete(string $key): bool;

    /**
     * Wipes clean the entire cache.
     *
     * @return bool True on success, false on failure
     */
    public function clear(): bool;

    /**
     * Determines whether an item is present in the cache.
     *
     * @param string $key The cache key
     * @return bool True if key exists, false otherwise
     */
    public function has(string $key): bool;

    /**
     * Obtains multiple cache items by their unique keys.
     *
     * @param iterable<string> $keys A list of keys
     * @param mixed $default Default value for keys that do not exist
     * @return iterable<string, mixed> Key => value pairs
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable;

    /**
     * Persists a set of key => value pairs in the cache.
     *
     * @param iterable<string, mixed> $values Key => value pairs to cache
     * @param int|null $ttl Optional TTL in seconds
     * @return bool True on success, false on failure
     */
    public function setMultiple(iterable $values, ?int $ttl = null): bool;

    /**
     * Deletes multiple cache items.
     *
     * @param iterable<string> $keys A list of keys to delete
     * @return bool True if all items were successfully removed
     */
    public function deleteMultiple(iterable $keys): bool;
}
