<?php

namespace PSinfoodservice\Tests\Unit\Dtos\Outgoing;

use PHPUnit\Framework\TestCase;
use PSinfoodservice\Dtos\Outgoing\AssetDto;

class AssetDtoTest extends TestCase
{
    public function test_from_data_maps_pascal_case_properties()
    {
        $data = [
            'Id' => 123,
            'FileId' => 456,
            'LogisticId' => 789,
            'AssetTypeId' => 1,
            'AssetType' => 'Product Image',
            'FacingTypeId' => 2,
            'FacingType' => 'Front',
            'AngleTypeId' => 3,
            'AngleType' => 'Center',
            'FormatTypeId' => 4,
            'FormatType' => 'JPG',
            'Label' => 'Main Product Image',
            'SourceId' => 5,
            'Source' => 'Manufacturer',
            'IsDefault' => true,
            'PixelWidth' => 1920,
            'PixelHeight' => 1080,
            'FileSize' => 102400,
            'FileExtension' => 'jpg',
            'FileName' => 'product.jpg',
            'Url' => 'https://example.com/product.jpg',
            'SecurityToken' => 'abc-123-def',
            'CreatedDate' => '2024-01-01T00:00:00',
            'ModifiedDate' => '2024-01-15T12:00:00'
        ];

        $dto = AssetDto::fromData($data);

        $this->assertSame(123, $dto->Id);
        $this->assertSame(456, $dto->FileId);
        $this->assertSame(789, $dto->LogisticId);
        $this->assertSame(1, $dto->AssetTypeId);
        $this->assertSame('Product Image', $dto->AssetType);
        $this->assertSame(2, $dto->FacingTypeId);
        $this->assertSame('Front', $dto->FacingType);
        $this->assertSame(3, $dto->AngleTypeId);
        $this->assertSame('Center', $dto->AngleType);
        $this->assertSame(4, $dto->FormatTypeId);
        $this->assertSame('JPG', $dto->FormatType);
        $this->assertSame('Main Product Image', $dto->Label);
        $this->assertSame(5, $dto->SourceId);
        $this->assertSame('Manufacturer', $dto->Source);
        $this->assertTrue($dto->IsDefault);
        $this->assertSame(1920, $dto->PixelWidth);
        $this->assertSame(1080, $dto->PixelHeight);
        $this->assertSame(102400, $dto->FileSize);
        $this->assertSame('jpg', $dto->FileExtension);
        $this->assertSame('product.jpg', $dto->FileName);
        $this->assertSame('https://example.com/product.jpg', $dto->Url);
        $this->assertSame('abc-123-def', $dto->SecurityToken);
        $this->assertSame('2024-01-01T00:00:00', $dto->CreatedDate);
        $this->assertSame('2024-01-15T12:00:00', $dto->ModifiedDate);
    }

    public function test_from_data_maps_camel_case_properties()
    {
        $data = [
            'id' => 123,
            'fileId' => 456,
            'logisticId' => 789,
            'assetTypeId' => 1,
            'assetType' => 'Document',
            'isDefault' => false,
            'pixelWidth' => 800,
            'pixelHeight' => 600,
            'url' => 'https://example.com/doc.pdf'
        ];

        $dto = AssetDto::fromData($data);

        $this->assertSame(123, $dto->Id);
        $this->assertSame(456, $dto->FileId);
        $this->assertSame(789, $dto->LogisticId);
        $this->assertSame(1, $dto->AssetTypeId);
        $this->assertSame('Document', $dto->AssetType);
        $this->assertFalse($dto->IsDefault);
        $this->assertSame(800, $dto->PixelWidth);
        $this->assertSame(600, $dto->PixelHeight);
        $this->assertSame('https://example.com/doc.pdf', $dto->Url);
    }

    public function test_from_data_uses_default_values_for_missing_properties()
    {
        $data = ['Id' => 999];

        $dto = AssetDto::fromData($data);

        $this->assertSame(999, $dto->Id);
        $this->assertSame(0, $dto->FileId);
        $this->assertSame(0, $dto->LogisticId);
        $this->assertNull($dto->AssetTypeId);
        $this->assertNull($dto->AssetType);
        $this->assertNull($dto->FacingTypeId);
        $this->assertNull($dto->Label);
        $this->assertFalse($dto->IsDefault);
        $this->assertNull($dto->PixelWidth);
        $this->assertNull($dto->Url);
    }

    public function test_from_data_accepts_stdclass_object()
    {
        $data = new \stdClass();
        $data->Id = 555;
        $data->FileId = 666;
        $data->AssetType = 'Logo';
        $data->IsDefault = true;

        $dto = AssetDto::fromData($data);

        $this->assertSame(555, $dto->Id);
        $this->assertSame(666, $dto->FileId);
        $this->assertSame('Logo', $dto->AssetType);
        $this->assertTrue($dto->IsDefault);
    }

    public function test_from_data_handles_partial_data()
    {
        $data = [
            'Id' => 100,
            'FileId' => 200,
            'Label' => 'Partial Asset',
            'IsDefault' => false
        ];

        $dto = AssetDto::fromData($data);

        $this->assertSame(100, $dto->Id);
        $this->assertSame(200, $dto->FileId);
        $this->assertSame('Partial Asset', $dto->Label);
        $this->assertFalse($dto->IsDefault);
        $this->assertNull($dto->AssetType);
        $this->assertNull($dto->Url);
    }

    public function test_from_data_preserves_null_values()
    {
        $data = [
            'Id' => 300,
            'FileId' => 400,
            'Label' => null,
            'Url' => null,
            'SecurityToken' => null
        ];

        $dto = AssetDto::fromData($data);

        $this->assertSame(300, $dto->Id);
        $this->assertSame(400, $dto->FileId);
        $this->assertNull($dto->Label);
        $this->assertNull($dto->Url);
        $this->assertNull($dto->SecurityToken);
    }

    public function test_from_data_handles_boolean_values_correctly()
    {
        $trueData = ['Id' => 1, 'IsDefault' => true];
        $falseData = ['Id' => 2, 'IsDefault' => false];

        $trueDto = AssetDto::fromData($trueData);
        $falseDto = AssetDto::fromData($falseData);

        $this->assertTrue($trueDto->IsDefault);
        $this->assertFalse($falseDto->IsDefault);
    }

    public function test_from_data_handles_image_dimensions()
    {
        $data = [
            'Id' => 1,
            'PixelWidth' => 3840,
            'PixelHeight' => 2160,
            'FileSize' => 5242880
        ];

        $dto = AssetDto::fromData($data);

        $this->assertSame(3840, $dto->PixelWidth);
        $this->assertSame(2160, $dto->PixelHeight);
        $this->assertSame(5242880, $dto->FileSize);
    }
}
