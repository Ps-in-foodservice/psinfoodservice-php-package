# Response DTOs

This directory contains typed Data Transfer Objects (DTOs) for API responses. These classes provide IntelliSense support and type safety when working with API responses.

## Available DTOs

### Brand DTOs
- **BrandDto**: Represents a brand with ownership and configuration information
  - Properties: Id, Name, Brandownerid, IsPubliclyVisible, DeclarationFormatTypeId, etc.

### Assortment DTOs
- **AssortmentDto**: Represents an assortment list with pagination support
  - Properties: Id, Name, PageNumber, PageSize, TotalPages, TotalItems, Items[]
- **AssortmentItemDto**: Represents an item within an assortment
  - Properties: Id, ArticleNumber, ArticleName, GTINCE, GTINHE, RelationName, etc.

### Impact Score DTOs
- **ProductImpactScoreListDto**: List of products with environmental impact scores
  - Properties: Results, Items[]
- **ProductImpactScoreItemDto**: Individual product with impact score data
  - Properties: LogisticId, ProductId, Name, GTIN, ImpactScore, Co2Emission, WaterUsage, etc.

### Lookup DTOs
- **LookupResultDto**: Results of a product lookup operation
  - Properties: PageNumber, PageSize, TotalPages, Changed[], Deleted[], NotFound[], NotChanged[]
  - ItemsCounts: ItemsChanged, ItemsDeleted, ItemsNotChanged, ItemsNotFound
- **RequestStatusItemDto**: Individual product item in lookup results
  - Properties: LogisticId, ProductId, Name, Number, GTIN, LastChanged, Reason, etc.

### Core Response DTOs
Located in root Dtos/Outgoing directory:
- **ResponseDto**: Standard API operation response
- **HeaderDto**: Response header with metadata
- **ValidationErrorDto**: Validation error details

## Usage

### Converting from API Response

All DTOs have a static `fromData()` method that converts stdClass objects or arrays to typed DTOs:

```php
// Example: Convert assortment response to typed DTO
$response = $client->assortment->getAssortmentList($assortmentId);
$assortmentDto = AssortmentDto::fromData($response);

// Now you have full type safety and IntelliSense
echo $assortmentDto->Name;
echo $assortmentDto->TotalItems;

foreach ($assortmentDto->Items as $item) {
    echo $item->ArticleName;
    echo $item->GTINCE;
}
```

### Brand Example

```php
$brands = $client->brands->All();

foreach ($brands as $brandData) {
    $brand = BrandDto::fromData($brandData);

    echo "Brand: {$brand->Name}\n";
    echo "Owner: {$brand->Brandownername}\n";
    echo "Public: " . ($brand->IsPubliclyVisible ? 'Yes' : 'No') . "\n";
}
```

### Impact Score Example

```php
$impactScores = $client->impactScore->getImpactScores($criteria);
$impactList = ProductImpactScoreListDto::fromData($impactScores);

echo "Total results: {$impactList->Results}\n";

foreach ($impactList->Items as $item) {
    echo "Product: {$item->Number}\n";
    echo "Impact Score: {$item->ImpactScore}\n";
    echo "CO2: {$item->Co2Emission}kg\n";
    echo "Water: {$item->WaterUsage}L\n";

    if ($item->IsOutlier) {
        echo "Outlier Reason: {$item->OutlierReason}\n";
    }
}
```

### Lookup Example

```php
$lookupRequest = new RequestLookupGtin();
$lookupRequest->Gtins = ['1234567890123', '9876543210987'];
$lookupRequest->FromDate = '2024-01-01';

$response = $client->lookups->Gtin($lookupRequest);
$lookupResult = LookupResultDto::fromData($response);

echo "Changed: {$lookupResult->ItemsChanged}\n";
echo "Deleted: {$lookupResult->ItemsDeleted}\n";
echo "Not Found: {$lookupResult->ItemsNotFound}\n";
echo "Not Changed: {$lookupResult->ItemsNotChanged}\n";

// Process changed items
if ($lookupResult->Changed) {
    foreach ($lookupResult->Changed as $item) {
        echo "Changed: {$item->GTIN} - {$item->Number}\n";
        echo "Last changed: {$item->LastChanged}\n";
    }
}

// Process not found GTINs
if ($lookupResult->NotFound) {
    foreach ($lookupResult->NotFound as $gtin) {
        echo "Not found: {$gtin}\n";
    }
}
```

### Assortment with Pagination Example

```php
$pageNumber = 1;
$pageSize = 50;

$response = $client->assortment->getAssortmentList($assortmentId, $pageNumber, $pageSize);
$assortment = AssortmentDto::fromData($response);

echo "Assortment: {$assortment->Name}\n";
echo "Page {$assortment->PageNumber} of {$assortment->TotalPages}\n";
echo "Total items: {$assortment->TotalItems}\n";

foreach ($assortment->Items as $item) {
    echo "- {$item->ArticleName} ({$item->GTINCE})\n";
    echo "  Brand: {$item->ArticleBrand}\n";
    echo "  Supplier: {$item->RelationName}\n";
}
```

## Benefits

1. **Type Safety**: IDEs can provide autocomplete and type checking
2. **Documentation**: Properties are clearly documented with PHPDoc
3. **Validation**: Easier to validate data structure
4. **Maintainability**: Changes to API responses are centralized in DTO classes
5. **Debugging**: Easier to inspect object properties compared to stdClass

## Case Sensitivity

The `fromData()` methods handle both PascalCase and camelCase property names automatically, so they work with both JSON and XML API responses:

```php
// Works with PascalCase (JSON typical)
$data = ['LogisticId' => 123, 'Name' => 'Product'];
$dto = RequestStatusItemDto::fromData($data);

// Also works with camelCase
$data = ['logisticId' => 123, 'name' => 'Product'];
$dto = RequestStatusItemDto::fromData($data);

// And lowercase (XML typical)
$data = ['logisticid' => 123, 'name' => 'Product'];
$dto = RequestStatusItemDto::fromData($data);
```

## Creating New DTOs

When creating new DTOs, follow this pattern:

```php
<?php

namespace PSinfoodservice\Dtos\Outgoing;

/**
 * Brief description of what this DTO represents
 */
class MyDto
{
    /** Property description */
    public type $PropertyName = defaultValue;

    /**
     * Create a MyDto from an array or stdClass object
     *
     * @param array|object $data The data to map from
     * @return self
     */
    public static function fromData($data): self
    {
        $dto = new self();
        $data = is_array($data) ? (object)$data : $data;

        $dto->PropertyName = $data->PropertyName ?? $data->propertyName ?? defaultValue;

        return $dto;
    }
}
```

## Future Enhancements

Additional DTOs that could be added:
- ProductSheetDto with full nested structure (LogisticDto, ProductDto, SpecificationDto)
- RelationDto for supplier/producer information
- Master data DTOs (ProductGroupDto, TargetMarketDto, etc.)
- Asset DTOs for image and file management

These would follow the same pattern as the existing DTOs.
