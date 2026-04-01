<?php

namespace PSinfoodservice\Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use PSinfoodservice\Cache\InMemoryCache;
use PSinfoodservice\Services\CachedMasterService;
use PSinfoodservice\Services\MasterService;

class CachedMasterServiceTest extends TestCase
{
    public function test_uses_cache_for_subsequent_calls()
    {
        $masterService = $this->createMock(MasterService::class);
        $masterData = (object) ['allergens' => [], 'nutrients' => []];

        // Should only be called once - second call uses cache
        $masterService->expects($this->once())
            ->method('getAllMasters')
            ->willReturn($masterData);

        $cachedMasters = new CachedMasterService($masterService);

        // First call hits the service
        $result1 = $cachedMasters->getAllMasters();

        // Second call should use cache (service not called again)
        $result2 = $cachedMasters->getAllMasters();

        $this->assertSame($result1, $result2);
        $this->assertEquals($masterData, $result1);
    }

    public function test_uses_custom_cache()
    {
        $masterService = $this->createMock(MasterService::class);
        $masterService->method('getAllMasters')->willReturn((object) ['data' => 'test']);

        $customCache = new InMemoryCache();
        $cachedMasters = new CachedMasterService($masterService, $customCache);

        $cachedMasters->getAllMasters();

        $this->assertSame($customCache, $cachedMasters->getCache());
        $this->assertTrue($customCache->has('ps_masters_all'));
    }

    public function test_clear_cache_removes_all_cached_data()
    {
        $masterService = $this->createMock(MasterService::class);
        $masterService->method('getAllMasters')->willReturn((object) ['data' => 'test']);

        $cachedMasters = new CachedMasterService($masterService);
        $cachedMasters->getAllMasters();

        $this->assertTrue($cachedMasters->getCache()->has('ps_masters_all'));

        $cachedMasters->clearCache();

        $this->assertFalse($cachedMasters->getCache()->has('ps_masters_all'));
    }

    public function test_clear_cache_for_specific_type()
    {
        $masterService = $this->createMock(MasterService::class);
        $masterService->method('getAllMasters')->willReturn((object) ['data' => 'all']);
        $masterService->method('getLogisticMasters')->willReturn((object) ['data' => 'logistic']);

        $cachedMasters = new CachedMasterService($masterService);
        $cachedMasters->getAllMasters();
        $cachedMasters->getLogisticMasters();

        $cachedMasters->clearCacheFor('all');

        $this->assertFalse($cachedMasters->getCache()->has('ps_masters_all'));
        $this->assertTrue($cachedMasters->getCache()->has('ps_masters_logistic'));
    }

    public function test_refresh_clears_cache_and_fetches_fresh()
    {
        $masterService = $this->createMock(MasterService::class);

        // First call returns 'old', second returns 'new'
        $masterService->expects($this->exactly(2))
            ->method('getAllMasters')
            ->willReturnOnConsecutiveCalls(
                (object) ['data' => 'old'],
                (object) ['data' => 'new']
            );

        $cachedMasters = new CachedMasterService($masterService);

        $old = $cachedMasters->getAllMasters();
        $new = $cachedMasters->refreshAllMasters();

        $this->assertEquals('old', $old->data);
        $this->assertEquals('new', $new->data);
    }

    public function test_can_set_custom_ttl()
    {
        $masterService = $this->createMock(MasterService::class);
        $cachedMasters = new CachedMasterService($masterService);

        $result = $cachedMasters->setDefaultTtl(7200);

        $this->assertSame($cachedMasters, $result);
    }

    public function test_can_replace_cache()
    {
        $masterService = $this->createMock(MasterService::class);
        $cachedMasters = new CachedMasterService($masterService);
        $newCache = new InMemoryCache();

        $result = $cachedMasters->setCache($newCache);

        $this->assertSame($cachedMasters, $result);
        $this->assertSame($newCache, $cachedMasters->getCache());
    }

    public function test_null_result_is_not_cached()
    {
        $masterService = $this->createMock(MasterService::class);

        // Both calls return null
        $masterService->expects($this->exactly(2))
            ->method('getAllMasters')
            ->willReturn(null);

        $cachedMasters = new CachedMasterService($masterService);

        // First call returns null
        $result1 = $cachedMasters->getAllMasters();

        // Since result was null, it shouldn't be cached
        // So second call should hit service again (hence expects(2))
        $result2 = $cachedMasters->getAllMasters();

        $this->assertNull($result1);
        $this->assertNull($result2);
    }

    public function test_caches_logistic_masters()
    {
        $masterService = $this->createMock(MasterService::class);
        $masterService->expects($this->once())
            ->method('getLogisticMasters')
            ->willReturn((object) ['data' => 'logistic']);

        $cachedMasters = new CachedMasterService($masterService);

        $cachedMasters->getLogisticMasters();
        $cachedMasters->getLogisticMasters(); // Should use cache

        $this->assertTrue($cachedMasters->getCache()->has('ps_masters_logistic'));
    }

    public function test_caches_product_masters()
    {
        $masterService = $this->createMock(MasterService::class);
        $masterService->expects($this->once())
            ->method('getProductMasters')
            ->willReturn((object) ['data' => 'product']);

        $cachedMasters = new CachedMasterService($masterService);

        $cachedMasters->getProductMasters();
        $cachedMasters->getProductMasters();

        $this->assertTrue($cachedMasters->getCache()->has('ps_masters_product'));
    }
}
