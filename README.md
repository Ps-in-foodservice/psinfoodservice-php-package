# PS in foodservice API Client for PHP

A comprehensive PHP client library for the PS in foodservice Web API (v7). This package simplifies integration with PS in foodservice services by providing a clean, type-safe interface for all API endpoints.

## Installation

```bash
composer require psinfoodservice/psinfoodserviceapi
```

## Requirements

- PHP 8.0 or higher
- Composer
- PS in foodservice account with API access

## Key Features

- Full API endpoint coverage (~95%)
- **Async/Concurrent requests** - Execute multiple API calls in parallel
- **Response caching** - Built-in caching for master/reference data
- **Request/Response logging** - Middleware for debugging and monitoring
- **Automatic token refresh** - Seamless token management
- **Retry with exponential backoff** - Resilient request handling
- **Rate limit handling** - Auto-wait on 429 responses
- Comprehensive error handling with trace IDs

## Documentation

For complete API documentation, visit the [PS in foodservice API Documentation](http://webapi.psinfoodservice.com/v7/swagger/index.html).

## Modules

| Module             | Description                                             |
| ------------------ | ------------------------------------------------------- |
| **authentication** | Login, logout, token management, webhooks               |
| **webApi**         | Core product data operations                            |
| **lookups**        | Track product updates (GTIN, PSID, ArticleNumber, etc.) |
| **brands**         | Brand information and management                        |
| **masters**        | Reference data (allergens, nutrients, countries, etc.)  |
| **assortment**     | Assortment list management                              |
| **assets**         | Asset (image/document) information                      |
| **relations**      | Producer and brand owner information                    |
| **files**          | File and image retrieval                                |
| **impactScore**    | Environmental impact scoring                            |
| **validation**     | Product data validation                                 |
| **mijnPS**         | MijnPS operations (assortment uploads)                  |
| **helper**         | Utility methods for data processing                     |

## Quick Start

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use PSinfoodservice\PSinfoodserviceClient;

$client = new PSinfoodserviceClient('preproduction');

// Login
$client->authentication->login('email@example.com', 'password');

// Get product
$product = $client->webApi->getProductSheet(59);
echo $product->summary->name[0]->value;

// Logout
$client->authentication->logoff();
```

## Examples

### Async/Concurrent Requests

```php
$async = $client->async();
$async->get('brands', 'Brand/All');
$async->get('masters', 'Master/All');
$results = $async->execute();
```

### Cached Master Data

```php
$cached = $client->cachedMasters();
$masters = $cached->getAllMasters(); // API call
$masters = $cached->getAllMasters(); // From cache
```

### Brand Management

```php
$brands = $client->brands->getAll();
$newBrands = $client->brands->getAllByDate('-1 week');
$brandId = $client->brands->createOrUpdateBrand(['Name' => 'My Brand']);
```

### MijnPS Uploads

```php
$client->mijnPS->uploadAssortment('guid-here', '/path/to/file.xlsx');
```

### Assets & Relations

```php
$assets = $client->assets->getAssetsFromLogistic(12345);
$producers = $client->relations->getProducers();
$brandOwners = $client->relations->getBrandOwners();
```

### Error Handling

```php
try {
    $client->authentication->login('email', 'pass');
} catch (\PSinfoodservice\Exceptions\PSApiException $e) {
    echo $e->getMessage();
    echo $e->getTraceId(); // For debugging
}
```

## Advanced Configuration

```php
$client = new PSinfoodserviceClient(
    environment: 'production',
    verifySSL: true,
    autoRefreshEnabled: true,
    retryConfig: [
        'enabled' => true,
        'max_retries' => 3,
        'rate_limit_auto_wait' => true,
        'logger' => $psrLogger
    ]
);
```

## Development & Testing

The package includes 310+ PHPUnit tests:

```bash
composer test
vendor/bin/phpunit --testdox
```

## Service Interfaces

For testability: `AuthenticationServiceInterface`, `BrandServiceInterface`, `LookupServiceInterface`, `CacheInterface`

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for details.

## Support

Contact: it@psinfoodservice.com
