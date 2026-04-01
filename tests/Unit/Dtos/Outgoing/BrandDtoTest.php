<?php

namespace PSinfoodservice\Tests\Unit\Dtos\Outgoing;

use PHPUnit\Framework\TestCase;
use PSinfoodservice\Dtos\Outgoing\BrandDto;

class BrandDtoTest extends TestCase
{
    public function test_from_data_maps_pascal_case_properties()
    {
        $data = [
            'Id' => 123,
            'Name' => 'Test Brand',
            'Brandownerid' => 456,
            'Brandownername' => 'Owner Name',
            'Brandownergln' => '1234567890123',
            'IsPrivateLabel' => true,
            'IsPubliclyVisible' => false,
            'Image' => 'https://example.com/logo.jpg',
            'ThirdPartyId' => 'ext-123',
            'IsVisibleInProducerDetail' => true,
            'DeclarationFormatTypeId' => 2,
            'AllowProducersToPublishSpecification' => false
        ];

        $dto = BrandDto::fromData($data);

        $this->assertSame(123, $dto->Id);
        $this->assertSame('Test Brand', $dto->Name);
        $this->assertSame(456, $dto->Brandownerid);
        $this->assertSame('Owner Name', $dto->Brandownername);
        $this->assertSame('1234567890123', $dto->Brandownergln);
        $this->assertTrue($dto->IsPrivateLabel);
        $this->assertFalse($dto->IsPubliclyVisible);
        $this->assertSame('https://example.com/logo.jpg', $dto->Image);
        $this->assertSame('ext-123', $dto->ThirdPartyId);
        $this->assertTrue($dto->IsVisibleInProducerDetail);
        $this->assertSame(2, $dto->DeclarationFormatTypeId);
        $this->assertFalse($dto->AllowProducersToPublishSpecification);
    }

    public function test_from_data_maps_lowercase_properties()
    {
        $data = [
            'id' => 789,
            'name' => 'Lowercase Brand',
            'brandownerid' => 111,
            'brandownername' => 'Lowercase Owner',
            'isprivatelabel' => false,
            'ispubliclyvisible' => true
        ];

        $dto = BrandDto::fromData($data);

        $this->assertSame(789, $dto->Id);
        $this->assertSame('Lowercase Brand', $dto->Name);
        $this->assertSame(111, $dto->Brandownerid);
        $this->assertSame('Lowercase Owner', $dto->Brandownername);
        $this->assertFalse($dto->IsPrivateLabel);
        $this->assertTrue($dto->IsPubliclyVisible);
    }

    public function test_from_data_uses_default_values_for_missing_properties()
    {
        $data = ['Id' => 456];

        $dto = BrandDto::fromData($data);

        $this->assertSame(456, $dto->Id);
        $this->assertSame(0, $dto->Brandownerid);
        $this->assertSame(0, $dto->DeclarationFormatTypeId);
        $this->assertNull($dto->Name);
        $this->assertNull($dto->Brandownername);
        $this->assertNull($dto->IsPrivateLabel);
        $this->assertNull($dto->IsPubliclyVisible);
    }

    public function test_from_data_accepts_stdclass_object()
    {
        $data = new \stdClass();
        $data->Id = 999;
        $data->Name = 'Object Brand';
        $data->Brandownerid = 888;
        $data->IsPrivateLabel = true;

        $dto = BrandDto::fromData($data);

        $this->assertSame(999, $dto->Id);
        $this->assertSame('Object Brand', $dto->Name);
        $this->assertSame(888, $dto->Brandownerid);
        $this->assertTrue($dto->IsPrivateLabel);
    }

    public function test_from_data_handles_partial_data()
    {
        $data = [
            'Id' => 100,
            'Name' => 'Partial Brand',
            'IsPubliclyVisible' => false
        ];

        $dto = BrandDto::fromData($data);

        $this->assertSame(100, $dto->Id);
        $this->assertSame('Partial Brand', $dto->Name);
        $this->assertFalse($dto->IsPubliclyVisible);
        $this->assertSame(0, $dto->Brandownerid);
        $this->assertNull($dto->Brandownername);
    }

    public function test_from_data_preserves_null_values()
    {
        $data = [
            'Id' => 200,
            'Name' => null,
            'Image' => null,
            'ThirdPartyId' => null
        ];

        $dto = BrandDto::fromData($data);

        $this->assertSame(200, $dto->Id);
        $this->assertNull($dto->Name);
        $this->assertNull($dto->Image);
        $this->assertNull($dto->ThirdPartyId);
    }

    public function test_from_data_handles_boolean_values_correctly()
    {
        $data = [
            'Id' => 300,
            'IsPrivateLabel' => true,
            'IsPubliclyVisible' => false,
            'IsVisibleInProducerDetail' => true,
            'AllowProducersToPublishSpecification' => false
        ];

        $dto = BrandDto::fromData($data);

        $this->assertTrue($dto->IsPrivateLabel);
        $this->assertFalse($dto->IsPubliclyVisible);
        $this->assertTrue($dto->IsVisibleInProducerDetail);
        $this->assertFalse($dto->AllowProducersToPublishSpecification);
    }
}
