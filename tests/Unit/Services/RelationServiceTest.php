<?php

namespace PSinfoodservice\Tests\Unit\Services;

use GuzzleHttp\Psr7\Response;
use PSinfoodservice\Exceptions\PSApiException;
use PSinfoodservice\Services\RelationService;
use PSinfoodservice\Tests\Support\TestCase;

class RelationServiceTest extends TestCase
{
    public function test_get_producers_returns_array_on_success()
    {
        $responseData = [
            'producers' => [
                ['id' => 1, 'name' => 'Producer A'],
                ['id' => 2, 'name' => 'Producer B'],
                ['id' => 3, 'name' => 'Producer C']
            ]
        ];

        $client = $this->createMockPSClient([
            $this->createJsonResponse($responseData)
        ]);

        $result = $client->relations->getProducers();

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
    }

    public function test_get_producers_returns_null_on_empty_response()
    {
        $client = $this->createMockPSClient([
            $this->createJsonResponse([])
        ]);

        $result = $client->relations->getProducers();

        $this->assertNull($result);
    }

    public function test_get_producers_handles_pascal_case_response()
    {
        $responseData = [
            'Producers' => [
                ['Id' => 1, 'Name' => 'Producer One'],
                ['Id' => 2, 'Name' => 'Producer Two']
            ]
        ];

        $client = $this->createMockPSClient([
            $this->createJsonResponse($responseData)
        ]);

        $result = $client->relations->getProducers();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    public function test_get_producers_throws_on_client_error()
    {
        $client = $this->createMockPSClient([
            $this->createErrorResponse('Unauthorized', 401)
        ]);

        $this->expectException(PSApiException::class);

        $client->relations->getProducers();
    }

    public function test_get_brand_owners_returns_array_on_success()
    {
        $responseData = [
            'brandOwners' => [
                ['id' => 10, 'name' => 'Brand Owner X'],
                ['id' => 20, 'name' => 'Brand Owner Y']
            ]
        ];

        $client = $this->createMockPSClient([
            $this->createJsonResponse($responseData)
        ]);

        $result = $client->relations->getBrandOwners();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    public function test_get_brand_owners_returns_null_on_empty_response()
    {
        $client = $this->createMockPSClient([
            $this->createJsonResponse([])
        ]);

        $result = $client->relations->getBrandOwners();

        $this->assertNull($result);
    }

    public function test_get_brand_owners_handles_pascal_case_response()
    {
        $responseData = [
            'BrandOwners' => [
                ['Id' => 100, 'Name' => 'Owner Alpha'],
                ['Id' => 200, 'Name' => 'Owner Beta']
            ]
        ];

        $client = $this->createMockPSClient([
            $this->createJsonResponse($responseData)
        ]);

        $result = $client->relations->getBrandOwners();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    public function test_get_brand_owners_throws_on_client_error()
    {
        $client = $this->createMockPSClient([
            $this->createErrorResponse('Forbidden', 403)
        ]);

        $this->expectException(PSApiException::class);

        $client->relations->getBrandOwners();
    }

    public function test_get_producers_handles_direct_array_response()
    {
        $responseData = [
            ['id' => 1, 'name' => 'Direct Producer 1'],
            ['id' => 2, 'name' => 'Direct Producer 2']
        ];

        $client = $this->createMockPSClient([
            $this->createJsonResponse($responseData)
        ]);

        $result = $client->relations->getProducers();

        $this->assertIsArray($result);
    }

    public function test_get_brand_owners_handles_direct_array_response()
    {
        $responseData = [
            ['id' => 1, 'name' => 'Direct Owner 1'],
            ['id' => 2, 'name' => 'Direct Owner 2']
        ];

        $client = $this->createMockPSClient([
            $this->createJsonResponse($responseData)
        ]);

        $result = $client->relations->getBrandOwners();

        $this->assertIsArray($result);
    }
}
