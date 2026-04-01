<?php

namespace PSinfoodservice\Tests\Unit\Helpers;

use GuzzleHttp\Psr7\Response;
use PSinfoodservice\Helpers\AsyncClient;
use PSinfoodservice\Tests\Support\TestCase;

class AsyncClientTest extends TestCase
{
    public function test_can_add_requests()
    {
        $client = $this->createMockPSClient([]);
        $async = new AsyncClient($client);

        $async->addRequest('test1', 'GET', 'Brand/All');
        $async->addRequest('test2', 'POST', 'Lookup/gtin', ['json' => []]);

        $this->assertSame(2, $async->count());
    }

    public function test_can_add_get_request()
    {
        $client = $this->createMockPSClient([]);
        $async = new AsyncClient($client);

        $async->get('brands', 'Brand/All');

        $this->assertSame(1, $async->count());
    }

    public function test_can_add_post_request()
    {
        $client = $this->createMockPSClient([]);
        $async = new AsyncClient($client);

        $async->post('lookup', 'Lookup/gtin', ['gtin' => '1234567890123']);

        $this->assertSame(1, $async->count());
    }

    public function test_clear_removes_all_requests()
    {
        $client = $this->createMockPSClient([]);
        $async = new AsyncClient($client);

        $async->get('test1', 'Brand/All');
        $async->get('test2', 'Master/All');

        $this->assertSame(2, $async->count());

        $async->clear();

        $this->assertSame(0, $async->count());
    }

    public function test_can_set_concurrency()
    {
        $client = $this->createMockPSClient([]);
        $async = new AsyncClient($client);

        $result = $async->setConcurrency(5);

        $this->assertSame($async, $result);
    }

    public function test_execute_returns_empty_array_when_no_requests()
    {
        $client = $this->createMockPSClient([]);
        $async = new AsyncClient($client);

        $results = $async->execute();

        $this->assertSame([], $results);
    }

    public function test_execute_returns_results_indexed_by_key()
    {
        $brandsResponse = ['brands' => [['Id' => 1, 'Name' => 'Test']]];

        $client = $this->createMockPSClient([
            $this->createJsonResponse($brandsResponse)
        ]);

        $async = new AsyncClient($client);
        $async->get('brands', 'Brand/All');

        $results = $async->execute();

        $this->assertArrayHasKey('brands', $results);
        $this->assertIsObject($results['brands']);
        $this->assertObjectHasProperty('brands', $results['brands']);
    }

    public function test_execute_clears_requests_after_execution()
    {
        $client = $this->createMockPSClient([
            $this->createJsonResponse(['data' => 'test'])
        ]);

        $async = new AsyncClient($client);
        $async->get('test', 'Brand/All');

        $this->assertSame(1, $async->count());

        $async->execute();

        $this->assertSame(0, $async->count());
    }

    public function test_fluent_interface()
    {
        $client = $this->createMockPSClient([]);
        $async = new AsyncClient($client);

        $result = $async
            ->setConcurrency(5)
            ->get('test1', 'Brand/All')
            ->post('test2', 'Lookup/gtin')
            ->clear();

        $this->assertSame($async, $result);
    }
}
