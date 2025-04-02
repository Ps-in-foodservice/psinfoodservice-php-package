# PS in foodservice API Client for PHP

A comprehensive PHP client library for the PS in foodservice Web API (v7). This package simplifies integration with PS in foodservice services by providing a clean, type-safe interface for all API endpoints.

## Installation

Install the package via Composer:

```bash
composer require psinfoodservice/psinfoodserviceapi
```

## Requirements

- PHP 7.4 or higher
- Composer
- PS in foodservice account with API access

## Quick Start

```php
<?php
// Load autoloader
require_once __DIR__ . '/vendor/autoload.php';

use PSinfoodservice\Domain\Language;
use PSinfoodservice\PSinfoodserviceClient;

// Initialize the client
$client = new PSinfoodserviceClient('preproduction'); // Options: 'preproduction', 'production'
$psid = 59;

try {
    // Authenticate
    $result = $client->authentication->login('your-email@example.com', 'your-password');
    echo "Authentication successful: Access token received.\n";
    
    // Get product information
    $productSheet = $client->webApi->getProductSheet($psid);
    if ($productSheet != null) {
        echo 'Product name: ' . $productSheet->summary->name[0]->value . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

## Key Features

- Easy authentication with PS in foodservice API
- Support for all API endpoints
- Helper methods for common operations
- Type-safe request and response handling
- Error handling and validation support

## Documentation

For complete API documentation, visit the [PS in foodservice API Documentation](http://webapi.psinfoodservice.com/v7/swagger/index.html).

## Modules

The client is organized into multiple modules, each handling a specific part of the API:

- **Authentication**: Login and token management
- **Masters**: Reference data
- **Assortment**: Assortment list management
- **Brands**: Brand information
- **Updates**: Track product updates
- **WebApi**: Core product data operations
- **ImpactScore**: Environmental impact scoring
- **Images**: Product image retrieval
- **Helper**: Utility methods for data processing

## Examples

### Authentication

```php
$client = new PSinfoodserviceClient('preproduction');
$result = $client->authentication->login('your-email@example.com', 'your-password');
```

### Retrieving Product Information

```php
// Get product sheet
$psid = 59;
$productSheet = $client->webApi->getProductSheet($psid);

// Get ingredients information
$ingredients = $client->helper->getIngredientsPreview($productSheet);
if ($ingredients != null) {
    echo 'Ingredients declaration: ' . $ingredients->declarationPreview . "\n";
}
```

### Retrieving My Products

```php
// Get all my products
$myproducts = $client->webApi->getMyProducts();
if ($myproducts != null) { 
    echo "<br /><br />Mijn producten:<br />";
    foreach ($myproducts as $product) {
        echo "Id: " . $product->LogisticId . "<br />EAN: " . $product->GTIN . "<br />LastChanged: " . $product->LastChanged . "<br />";
    }
}
```

### Working with Allergens

```php
// Get extended allergen information in table format
$allergens = $client->helper->getAllergensPreview(
    $productSheet, 
    true,  // extended information
    Language::nl,  // language
    Outputstyle::table  // output style (table, bootstrap)
);

// Get simplified allergen information
$simpleAllergens = $client->helper->getAllergensPreview(
    $productSheet, 
    false,  // simplified information
    Language::nl, 
    Outputstyle::table
);
```

### Retrieving Nutritional Information

```php
$nutrients = $client->helper->getNutrientsPreview(
    $productSheet,
    Language::nl,
    Outputstyle::table
);
```

### Working with Preparation Information

```php
$preparationInformations = $client->helper->getPreparationInformationPreview(
    $productSheet, 
    Language::nl,  // language
);
if ($preparationInformations != null) {
    foreach ($preparationInformations as $info) {
        echo 'Type: ' . $info->preparationType . "\n";
        echo 'Description: ' . $info->description . "\n";
    }
}
```

### Getting Impact Scores

```php
// Get all impact scores
$impactScoresResults = $client->impactScore->AllScores();
foreach ($impactScoresResults as $result) {
    echo "Id: " . $result->id . "\n";
    echo "ImpactScore: " . $result->score . "\n";
    echo "CO2 FarmToFarm: " . $result->farmToFarm . "\n";
}

// Get specific product impact score
$impactscore = $client->impactScore->GetScore($psid);
echo "ImpactScore: " . $impactscore->score . "\n";
```

### Working with Assortment Lists

```php
// Get all assortment lists
$assortmentLists = $client->assortment->getAssortmentLists();
foreach ($assortmentLists as $result) {
    echo "Id: " . $result->id . "\n";
    echo "Name: " . $result->name . "\n";
    echo "Type: " . $result->assortmentType->name[0]->value . "\n";
}

// Get a specific assortment list
$assortmentList = $client->assortment->getAssortmentList('00000000-0000-0000-0000-000000000000');
echo "Items:\n";
if ($assortmentList->items != null) {
    foreach ($assortmentList->items as $item) {
        echo "EAN: " . $item->gtince . "\n";
        echo "ArticleNumber: " . $item->articleNumber . "\n";
    }
}
```

### Tracking Updates

```php
// Get updates based on EAN codes
$updates = $client->updates->Ean((
    new RequestUpdateEAN())
    ->setSearchCriteria(['1236547892138', '12365478921381', '1236547892139'])
    ->setLastUpdatedAfter(date('c', strtotime('-3 days')))
    ->setTargetMarket(1)
);

echo "Changed: " . count($updates->Changed) . "\n";
echo "Deleted: " . count($updates->Deleted) . "\n";
echo "Not Found: " . count($updates->NotFound) . "\n";
echo "Not Changed: " . count($updates->NotChanged) . "\n";
```

### Retrieving Master Data

```php
// Get tax rates
$masters = $client->masters->GetAllMasters();
$taxrates = $masters->taxRates;
foreach ($taxrates as $taxrate) {
    echo "Id: " . $taxrate->id . "\n";
    echo "Name: " . $taxrate->name[0]->value . "\n";
}

// Get logistic shelf life options
$logisticMasters = $client->masters->GetLogisticMasters();
$shelfLifes = $logisticMasters->shelfLifes;
foreach ($shelfLifes as $shelfLife) {
    echo "Id: " . $shelfLife->id . "\n";
    echo "Name: " . $shelfLife->name[0]->value . "\n";
}
```

### Working with Images

```php
// Get product image
$imageData = $client->images->getImage(1234, '00000000-0000-0000-0000-000000000000');
$base64Image = base64_encode($imageData);
echo '<img src="data:image/png;base64,' . $base64Image . '" alt="Product Image">';
```

## Error Handling

The package includes comprehensive error handling:

```php
try {
    $result = $client->authentication->login('email@example.com', 'password');
    // Use the API...
} catch (\PSinfoodservice\Exceptions\PSApiException $e) {
    // Handle API-specific errors
    echo "API Error: " . $e->getMessage() . "\n";
    echo "HTTP Status: " . $e->getCode() . "\n";
    echo "Trace ID: " . $e->getTraceId() . "\n";
} catch (\GuzzleHttp\Exception\ClientException $e) {
    // Handle HTTP client errors (4xx)
    echo "Client Error: " . $e->getMessage() . "\n";
} catch (\GuzzleHttp\Exception\ServerException $e) {
    // Handle HTTP server errors (5xx)
    echo "Server Error: " . $e->getMessage() . "\n";
} catch (\Exception $e) {
    // Handle other exceptions
    echo "Error: " . $e->getMessage() . "\n";
}
```

## License

You must have a PS in foodservice account to use this package. For more information, visit https://psinfoodservice.com

## Support

For questions or support requests, please contact us at info@psinfoodservice.com.