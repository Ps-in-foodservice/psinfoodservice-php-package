<?php

namespace PSinfoodservice\Tests\Unit\Cache;

use PHPUnit\Framework\TestCase;
use PSinfoodservice\Cache\InMemoryCache;

class InMemoryCacheTest extends TestCase
{
    private InMemoryCache $cache;

    protected function setUp(): void
    {
        $this->cache = new InMemoryCache();
    }

    public function test_set_and_get_value()
    {
        $this->cache->set('key', 'value');

        $this->assertSame('value', $this->cache->get('key'));
    }

    public function test_get_returns_default_for_missing_key()
    {
        $result = $this->cache->get('nonexistent', 'default');

        $this->assertSame('default', $result);
    }

    public function test_get_returns_null_default_for_missing_key()
    {
        $result = $this->cache->get('nonexistent');

        $this->assertNull($result);
    }

    public function test_has_returns_true_for_existing_key()
    {
        $this->cache->set('key', 'value');

        $this->assertTrue($this->cache->has('key'));
    }

    public function test_has_returns_false_for_missing_key()
    {
        $this->assertFalse($this->cache->has('nonexistent'));
    }

    public function test_delete_removes_key()
    {
        $this->cache->set('key', 'value');
        $this->cache->delete('key');

        $this->assertFalse($this->cache->has('key'));
    }

    public function test_clear_removes_all_keys()
    {
        $this->cache->set('key1', 'value1');
        $this->cache->set('key2', 'value2');
        $this->cache->clear();

        $this->assertFalse($this->cache->has('key1'));
        $this->assertFalse($this->cache->has('key2'));
    }

    public function test_expired_items_are_removed()
    {
        $this->cache->set('key', 'value', 1);

        $this->assertTrue($this->cache->has('key'));

        sleep(2);

        $this->assertFalse($this->cache->has('key'));
    }

    public function test_items_without_ttl_never_expire()
    {
        $this->cache->set('key', 'value', null);

        $this->assertTrue($this->cache->has('key'));
        $this->assertSame('value', $this->cache->get('key'));
    }

    public function test_set_multiple()
    {
        $this->cache->setMultiple([
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ]);

        $this->assertSame('value1', $this->cache->get('key1'));
        $this->assertSame('value2', $this->cache->get('key2'));
        $this->assertSame('value3', $this->cache->get('key3'));
    }

    public function test_get_multiple()
    {
        $this->cache->set('key1', 'value1');
        $this->cache->set('key2', 'value2');

        $result = $this->cache->getMultiple(['key1', 'key2', 'key3'], 'default');

        $this->assertSame('value1', $result['key1']);
        $this->assertSame('value2', $result['key2']);
        $this->assertSame('default', $result['key3']);
    }

    public function test_delete_multiple()
    {
        $this->cache->set('key1', 'value1');
        $this->cache->set('key2', 'value2');
        $this->cache->set('key3', 'value3');

        $this->cache->deleteMultiple(['key1', 'key2']);

        $this->assertFalse($this->cache->has('key1'));
        $this->assertFalse($this->cache->has('key2'));
        $this->assertTrue($this->cache->has('key3'));
    }

    public function test_count_returns_number_of_items()
    {
        $this->cache->set('key1', 'value1');
        $this->cache->set('key2', 'value2');

        $this->assertSame(2, $this->cache->count());
    }

    public function test_keys_returns_all_keys()
    {
        $this->cache->set('key1', 'value1');
        $this->cache->set('key2', 'value2');

        $keys = $this->cache->keys();

        $this->assertContains('key1', $keys);
        $this->assertContains('key2', $keys);
        $this->assertCount(2, $keys);
    }

    public function test_can_store_complex_values()
    {
        $object = (object) ['name' => 'test', 'value' => 123];
        $array = ['nested' => ['data' => true]];

        $this->cache->set('object', $object);
        $this->cache->set('array', $array);

        $this->assertEquals($object, $this->cache->get('object'));
        $this->assertEquals($array, $this->cache->get('array'));
    }

    public function test_overwrite_existing_key()
    {
        $this->cache->set('key', 'value1');
        $this->cache->set('key', 'value2');

        $this->assertSame('value2', $this->cache->get('key'));
    }
}
