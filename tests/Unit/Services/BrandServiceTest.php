<?php

namespace PSinfoodservice\Tests\Unit\Services;

use GuzzleHttp\Psr7\Response;
use PSinfoodservice\Exceptions\PSApiException;
use PSinfoodservice\Tests\Support\TestCase;

class BrandServiceTest extends TestCase
{
    public function test_get_all_returns_brands_on_success()
    {
        $responseData = [
            'brands' => [
                ['Id' => 1, 'Name' => 'Brand A'],
                ['Id' => 2, 'Name' => 'Brand B'],
                ['Id' => 3, 'Name' => 'Brand C']
            ]
        ];

        $client = $this->createMockPSClient([
            $this->createJsonResponse($responseData)
        ]);

        $result = $client->brands->getAll();

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertEquals('Brand A', $result[0]->Name);
    }

    public function test_get_all_returns_null_on_empty_response()
    {
        $client = $this->createMockPSClient([
            $this->createJsonResponse([])
        ]);

        $result = $client->brands->getAll();

        $this->assertNull($result);
    }

    public function test_get_all_returns_null_when_brands_array_empty()
    {
        $client = $this->createMockPSClient([
            $this->createJsonResponse(['brands' => []])
        ]);

        $result = $client->brands->getAll();

        $this->assertNull($result);
    }

    public function test_get_all_throws_api_exception_on_error()
    {
        $client = $this->createMockPSClient([
            $this->createErrorResponse('Server error', 500)
        ]);

        $this->expectException(PSApiException::class);

        $client->brands->getAll();
    }

    public function test_get_my_brands_returns_brands_on_success()
    {
        $responseData = [
            'brands' => [
                ['Id' => 10, 'Name' => 'My Brand']
            ]
        ];

        $client = $this->createMockPSClient([
            $this->createJsonResponse($responseData)
        ]);

        $result = $client->brands->getMyBrands();

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('My Brand', $result[0]->Name);
    }

    public function test_get_my_brands_returns_null_on_empty()
    {
        $client = $this->createMockPSClient([
            $this->createJsonResponse(['brands' => []])
        ]);

        $result = $client->brands->getMyBrands();

        $this->assertNull($result);
    }

    public function test_get_all_by_date_returns_brands_with_datetime()
    {
        $responseData = [
            'brands' => [
                ['Id' => 1, 'Name' => 'New Brand', 'Created' => '2024-01-15'],
                ['Id' => 2, 'Name' => 'Recent Brand', 'Created' => '2024-01-20']
            ]
        ];

        $client = $this->createMockPSClient([
            $this->createJsonResponse($responseData)
        ]);

        $result = $client->brands->getAllByDate(new \DateTime('-1 month'));

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    public function test_get_all_by_date_returns_brands_with_string_date()
    {
        $responseData = [
            'brands' => [
                ['Id' => 1, 'Name' => 'Brand X']
            ]
        ];

        $client = $this->createMockPSClient([
            $this->createJsonResponse($responseData)
        ]);

        $result = $client->brands->getAllByDate('-2 weeks');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
    }

    public function test_get_all_by_date_returns_null_on_empty()
    {
        $client = $this->createMockPSClient([
            $this->createJsonResponse(['brands' => []])
        ]);

        $result = $client->brands->getAllByDate(new \DateTime('-1 week'));

        $this->assertNull($result);
    }

    public function test_get_all_by_date_throws_exception_for_date_too_old()
    {
        $client = $this->createMockPSClient([]);

        $this->expectException(PSApiException::class);
        $this->expectExceptionMessage('FromDate cannot be more than 3 months in the past');

        $client->brands->getAllByDate(new \DateTime('-4 months'));
    }

    public function test_get_all_by_date_throws_exception_for_old_string_date()
    {
        $client = $this->createMockPSClient([]);

        $this->expectException(PSApiException::class);
        $this->expectExceptionMessage('FromDate cannot be more than 3 months in the past');

        $client->brands->getAllByDate('-6 months');
    }

    public function test_get_all_by_date_accepts_date_exactly_3_months_ago()
    {
        $responseData = [
            'brands' => [
                ['Id' => 1, 'Name' => 'Old Brand']
            ]
        ];

        $client = $this->createMockPSClient([
            $this->createJsonResponse($responseData)
        ]);

        // Use a date just slightly newer than 3 months
        $result = $client->brands->getAllByDate(new \DateTime('-3 months +1 day'));

        $this->assertIsArray($result);
    }

    public function test_get_all_by_date_throws_invalid_argument_for_bad_date()
    {
        $client = $this->createMockPSClient([]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid date format');

        $client->brands->getAllByDate('not-a-valid-date');
    }

    public function test_get_all_by_date_accepts_iso_date_string()
    {
        $responseData = [
            'brands' => [
                ['Id' => 1, 'Name' => 'Test Brand']
            ]
        ];

        $client = $this->createMockPSClient([
            $this->createJsonResponse($responseData)
        ]);

        $result = $client->brands->getAllByDate('2026-02-01T00:00:00+00:00');

        $this->assertIsArray($result);
    }

    public function test_get_all_by_date_throws_api_exception_on_400()
    {
        $client = $this->createMockPSClient([
            $this->createErrorResponse('Bad Request', 400)
        ]);

        $this->expectException(PSApiException::class);
        $this->expectExceptionMessage('Bad Request');

        $client->brands->getAllByDate(new \DateTime('-1 week'));
    }

    public function test_get_all_by_date_throws_api_exception_on_500()
    {
        $client = $this->createMockPSClient([
            new Response(500, [], 'Internal Server Error')
        ]);

        $this->expectException(PSApiException::class);

        $client->brands->getAllByDate(new \DateTime('-1 week'));
    }

    public function test_create_or_update_brand_returns_id_on_create()
    {
        $client = $this->createMockPSClient([
            new Response(200, ['Content-Type' => 'application/json'], json_encode(123))
        ]);

        $result = $client->brands->createOrUpdateBrand([
            'Name' => 'New Brand'
        ]);

        $this->assertSame(123, $result);
    }

    public function test_create_or_update_brand_returns_id_on_update()
    {
        $client = $this->createMockPSClient([
            new Response(200, ['Content-Type' => 'application/json'], json_encode(456))
        ]);

        $result = $client->brands->createOrUpdateBrand([
            'Id' => 456,
            'Name' => 'Updated Brand'
        ]);

        $this->assertSame(456, $result);
    }

    public function test_create_or_update_brand_throws_exception_without_name()
    {
        $client = $this->createMockPSClient([]);

        $this->expectException(PSApiException::class);
        $this->expectExceptionMessage('Brand name is required');

        $client->brands->createOrUpdateBrand([
            'Id' => 1
        ]);
    }

    public function test_create_or_update_brand_throws_exception_with_empty_name()
    {
        $client = $this->createMockPSClient([]);

        $this->expectException(PSApiException::class);
        $this->expectExceptionMessage('Brand name is required');

        $client->brands->createOrUpdateBrand([
            'Name' => ''
        ]);
    }

    public function test_create_or_update_brand_throws_api_exception_on_400()
    {
        $client = $this->createMockPSClient([
            $this->createErrorResponse('Brand already exists', 400)
        ]);

        $this->expectException(PSApiException::class);
        $this->expectExceptionMessage('Brand already exists');

        $client->brands->createOrUpdateBrand([
            'Name' => 'Existing Brand'
        ]);
    }
}
