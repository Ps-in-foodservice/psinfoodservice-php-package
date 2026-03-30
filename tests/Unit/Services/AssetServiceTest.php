<?php

namespace PSinfoodservice\Tests\Unit\Services;

use GuzzleHttp\Psr7\Response;
use PSinfoodservice\Dtos\Outgoing\AssetDto;
use PSinfoodservice\Exceptions\PSApiException;
use PSinfoodservice\Services\AssetService;
use PSinfoodservice\Tests\Support\TestCase;

class AssetServiceTest extends TestCase
{
    public function test_get_asset_returns_asset_dto_on_success()
    {
        $responseData = [
            'Id' => 123,
            'FileId' => 456,
            'LogisticId' => 789,
            'AssetType' => 'Product Image',
            'IsDefault' => true
        ];

        $client = $this->createMockPSClient([
            $this->createJsonResponse($responseData)
        ]);

        $result = $client->assets->getAsset(123);

        $this->assertInstanceOf(AssetDto::class, $result);
        $this->assertSame(123, $result->Id);
        $this->assertSame(456, $result->FileId);
        $this->assertSame('Product Image', $result->AssetType);
        $this->assertTrue($result->IsDefault);
    }

    public function test_get_asset_returns_null_on_404()
    {
        $client = $this->createMockPSClient([
            $this->createErrorResponse('Not Found', 404)
        ]);

        $result = $client->assets->getAsset(999);

        $this->assertNull($result);
    }

    public function test_get_asset_returns_null_on_empty_response()
    {
        $client = $this->createMockPSClient([
            $this->createJsonResponse([])
        ]);

        $result = $client->assets->getAsset(123);

        $this->assertNull($result);
    }

    public function test_get_asset_throws_exception_for_invalid_id()
    {
        $client = $this->createMockPSClient([]);

        $this->expectException(PSApiException::class);
        $this->expectExceptionMessage('Asset ID must be greater than 0');

        $client->assets->getAsset(0);
    }

    public function test_get_asset_throws_exception_for_negative_id()
    {
        $client = $this->createMockPSClient([]);

        $this->expectException(PSApiException::class);
        $this->expectExceptionMessage('Asset ID must be greater than 0');

        $client->assets->getAsset(-5);
    }

    public function test_get_assets_from_logistic_returns_array_on_success()
    {
        $responseData = [
            ['Id' => 1, 'FileId' => 101, 'AssetType' => 'Image'],
            ['Id' => 2, 'FileId' => 102, 'AssetType' => 'Document'],
            ['Id' => 3, 'FileId' => 103, 'AssetType' => 'Logo']
        ];

        $client = $this->createMockPSClient([
            $this->createJsonResponse($responseData)
        ]);

        $result = $client->assets->getAssetsFromLogistic(12345);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertInstanceOf(AssetDto::class, $result[0]);
        $this->assertSame(1, $result[0]->Id);
        $this->assertSame('Image', $result[0]->AssetType);
        $this->assertSame(2, $result[1]->Id);
        $this->assertSame('Document', $result[1]->AssetType);
    }

    public function test_get_assets_from_logistic_returns_null_on_empty()
    {
        $client = $this->createMockPSClient([
            $this->createJsonResponse([])
        ]);

        $result = $client->assets->getAssetsFromLogistic(12345);

        $this->assertNull($result);
    }

    public function test_get_assets_from_logistic_returns_null_on_404()
    {
        $client = $this->createMockPSClient([
            $this->createErrorResponse('Not Found', 404)
        ]);

        $result = $client->assets->getAssetsFromLogistic(99999);

        $this->assertNull($result);
    }

    public function test_get_assets_from_logistic_throws_for_invalid_id()
    {
        $client = $this->createMockPSClient([]);

        $this->expectException(PSApiException::class);
        $this->expectExceptionMessage('Logistic ID must be greater than 0');

        $client->assets->getAssetsFromLogistic(0);
    }

    public function test_get_assets_from_logistic_by_language_returns_array()
    {
        $responseData = [
            ['Id' => 1, 'FileId' => 101, 'Label' => 'Productafbeelding'],
            ['Id' => 2, 'FileId' => 102, 'Label' => 'Document']
        ];

        $client = $this->createMockPSClient([
            $this->createJsonResponse($responseData)
        ]);

        $result = $client->assets->getAssetsFromLogisticByLanguage('NL', 12345);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertSame('Productafbeelding', $result[0]->Label);
    }

    public function test_get_assets_from_logistic_by_language_throws_for_empty_language()
    {
        $client = $this->createMockPSClient([]);

        $this->expectException(PSApiException::class);
        $this->expectExceptionMessage('Language must not be empty');

        $client->assets->getAssetsFromLogisticByLanguage('', 12345);
    }

    public function test_get_assets_from_logistic_by_language_throws_for_invalid_id()
    {
        $client = $this->createMockPSClient([]);

        $this->expectException(PSApiException::class);
        $this->expectExceptionMessage('Logistic ID must be greater than 0');

        $client->assets->getAssetsFromLogisticByLanguage('NL', -1);
    }
}
