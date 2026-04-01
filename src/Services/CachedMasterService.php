<?php

declare(strict_types=1);
namespace PSinfoodservice\Services;

use PSinfoodservice\Contracts\CacheInterface;
use PSinfoodservice\Cache\InMemoryCache;

/**
 * Master service wrapper with built-in caching.
 *
 * This class wraps the MasterService and adds caching capabilities
 * to reduce redundant API calls for reference data that rarely changes.
 *
 * Master data (allergens, nutrients, countries, etc.) typically doesn't
 * change frequently, making it ideal for caching.
 *
 * @example
 * ```php
 * // Using default in-memory cache
 * $cachedMasters = new CachedMasterService($client->masters);
 *
 * // First call hits the API
 * $masters = $cachedMasters->getAllMasters();
 *
 * // Second call returns cached data
 * $mastersAgain = $cachedMasters->getAllMasters();
 *
 * // Using custom cache (e.g., Redis via PSR-16 adapter)
 * $redisCache = new RedisCache();
 * $cachedMasters = new CachedMasterService($client->masters, $redisCache, 3600);
 * ```
 */
class CachedMasterService
{
    private CacheInterface $cache;
    private int $defaultTtl;
    private string $cachePrefix = 'ps_masters_';

    /**
     * Initialize the cached master service.
     *
     * @param MasterService $masterService The underlying master service
     * @param CacheInterface|null $cache Cache implementation (defaults to InMemoryCache)
     * @param int $defaultTtl Default cache TTL in seconds (default: 3600 = 1 hour)
     */
    public function __construct(
        private MasterService $masterService,
        ?CacheInterface $cache = null,
        int $defaultTtl = 3600
    ) {
        $this->cache = $cache ?? new InMemoryCache();
        $this->defaultTtl = $defaultTtl;
    }

    /**
     * Get or fetch all masters with caching.
     *
     * @param int|null $ttl Optional custom TTL for this request
     * @return object|null
     */
    public function getAllMasters(?int $ttl = null): ?object
    {
        return $this->cached('all', fn() => $this->masterService->getAllMasters(), $ttl);
    }

    /**
     * Get or fetch logistic masters with caching.
     *
     * @param int|null $ttl Optional custom TTL for this request
     * @return object|null
     */
    public function getLogisticMasters(?int $ttl = null): ?object
    {
        return $this->cached('logistic', fn() => $this->masterService->getLogisticMasters(), $ttl);
    }

    /**
     * Get or fetch product masters with caching.
     *
     * @param int|null $ttl Optional custom TTL for this request
     * @return object|null
     */
    public function getProductMasters(?int $ttl = null): ?object
    {
        return $this->cached('product', fn() => $this->masterService->getProductMasters(), $ttl);
    }

    /**
     * Get or fetch storage masters with caching.
     *
     * @param int|null $ttl Optional custom TTL for this request
     * @return object|null
     */
    public function getStorageMasters(?int $ttl = null): ?object
    {
        return $this->cached('storage', fn() => $this->masterService->getStorageMasters(), $ttl);
    }

    /**
     * Get or fetch specification masters with caching.
     *
     * @param int|null $ttl Optional custom TTL for this request
     * @return object|null
     */
    public function getSpecificationMasters(?int $ttl = null): ?object
    {
        return $this->cached('specification', fn() => $this->masterService->getSpecificationMasters(), $ttl);
    }

    /**
     * Get or fetch profile masters with caching.
     *
     * @param int|null $ttl Optional custom TTL for this request
     * @return object|null
     */
    public function getProfileMasters(?int $ttl = null): ?object
    {
        return $this->cached('profile', fn() => $this->masterService->getProfileMasters(), $ttl);
    }

    /**
     * Clear all cached master data.
     *
     * @return bool
     */
    public function clearCache(): bool
    {
        $keys = [
            $this->cachePrefix . 'all',
            $this->cachePrefix . 'logistic',
            $this->cachePrefix . 'product',
            $this->cachePrefix . 'storage',
            $this->cachePrefix . 'specification',
            $this->cachePrefix . 'profile',
        ];

        return $this->cache->deleteMultiple($keys);
    }

    /**
     * Clear cache for a specific master type.
     *
     * @param string $type Master type (all, logistic, product, storage, specification, profile)
     * @return bool
     */
    public function clearCacheFor(string $type): bool
    {
        return $this->cache->delete($this->cachePrefix . $type);
    }

    /**
     * Force refresh all masters (clears cache and fetches fresh data).
     *
     * @return object|null
     */
    public function refreshAllMasters(): ?object
    {
        $this->clearCacheFor('all');
        return $this->getAllMasters();
    }

    /**
     * Get the underlying cache instance.
     *
     * @return CacheInterface
     */
    public function getCache(): CacheInterface
    {
        return $this->cache;
    }

    /**
     * Set a custom cache instance.
     *
     * @param CacheInterface $cache
     * @return self
     */
    public function setCache(CacheInterface $cache): self
    {
        $this->cache = $cache;
        return $this;
    }

    /**
     * Set the default TTL for cache entries.
     *
     * @param int $ttl TTL in seconds
     * @return self
     */
    public function setDefaultTtl(int $ttl): self
    {
        $this->defaultTtl = $ttl;
        return $this;
    }

    /**
     * Internal method to handle caching logic.
     *
     * @param string $key Cache key suffix
     * @param callable $fetcher Function to fetch data if not cached
     * @param int|null $ttl Optional custom TTL
     * @return mixed
     */
    private function cached(string $key, callable $fetcher, ?int $ttl = null): mixed
    {
        $cacheKey = $this->cachePrefix . $key;

        if ($this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $data = $fetcher();

        if ($data !== null) {
            $this->cache->set($cacheKey, $data, $ttl ?? $this->defaultTtl);
        }

        return $data;
    }
}
