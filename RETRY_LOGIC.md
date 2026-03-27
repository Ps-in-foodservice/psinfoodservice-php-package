# Retry Logic

The PS in Foodservice PHP SDK includes automatic retry logic for handling temporary failures such as server errors, network timeouts, and connection issues.

## Overview

Network requests can fail for various temporary reasons:
- **Server overload** (503 Service Unavailable)
- **Temporary server errors** (500 Internal Server Error)
- **Gateway timeouts** (502 Bad Gateway, 504 Gateway Timeout)
- **Network connectivity issues** (Connection timeouts)

The SDK automatically retries these requests using an exponential backoff strategy to avoid overwhelming the server.

## Default Behavior

By default, retry logic is **enabled** with these settings:
- **Maximum retries**: 3 attempts
- **Base delay**: 1000ms (1 second)
- **Retry strategy**: Exponential backoff (1s, 2s, 4s)
- **Retryable errors**: 500, 502, 503, 504, Connection timeouts

### Exponential Backoff

The delay between retries increases exponentially:
- **1st retry**: 1 second delay
- **2nd retry**: 2 seconds delay
- **3rd retry**: 4 seconds delay

This prevents overwhelming a struggling server while still providing resilience.

## Basic Usage

### Automatic Mode (Default)

Retries happen automatically without any code changes:

```php
use PSinfoodservice\PSinfoodserviceClient;
use PSinfoodservice\Domain\Environment;

$client = new PSinfoodserviceClient(Environment::production);
$client->authentication->login('username', 'password');

// This request will automatically retry up to 3 times on failure
$products = $client->webApi->getMyProducts();
```

### Custom Configuration

Configure retry behavior during client initialization:

```php
$client = new PSinfoodserviceClient(
    Environment::production,
    null,  // apiPrefix
    true,  // verifySSL
    true,  // autoRefreshEnabled
    [      // retryConfig
        'enabled' => true,
        'max_retries' => 5,
        'retry_delay' => 2000  // 2 seconds base delay
    ]
);

// Retries: 2s, 4s, 8s, 16s, 32s
```

### Disable Retries

```php
$client = new PSinfoodserviceClient(
    Environment::production,
    null,
    true,
    true,
    ['enabled' => false]  // Disable retry logic
);

// No retries will be attempted
```

## Configuration Options

### Runtime Configuration

You can change retry settings after client initialization:

```php
// Enable/disable retry logic
$client->setRetryEnabled(true);

// Set maximum retries
$client->setMaxRetries(5);

// Set base delay in milliseconds
$client->setRetryDelay(1500);  // 1.5 seconds

// Get current settings
echo "Retry enabled: " . ($client->isRetryEnabled() ? 'Yes' : 'No') . "\n";
echo "Max retries: " . $client->getMaxRetries() . "\n";
echo "Base delay: " . $client->getRetryDelay() . "ms\n";
```

### Per-Request Retry Override

Disable retries for specific requests (advanced usage):

```php
// This requires directly using Guzzle client
$response = $client->getHttpClient()->get(
    $client->buildApiPath('MyProducts'),
    ['retry_enabled' => false]  // Disable retry for this request only
);

// Or set custom max retries for a specific request
$response = $client->getHttpClient()->post(
    $client->buildApiPath('productsheet'),
    [
        'json' => $productSheet,
        'max_retries' => 1  // Only retry once for this request
    ]
);
```

## Logging

### PSR-3 Logger Integration

The SDK supports PSR-3 compatible loggers (Monolog, etc.) for tracking retry attempts:

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Create a PSR-3 logger
$logger = new Logger('ps-api');
$logger->pushHandler(new StreamHandler('path/to/api.log', Logger::WARNING));

// Pass logger to client
$client = new PSinfoodserviceClient(
    Environment::production,
    null,
    true,
    true,
    [
        'enabled' => true,
        'logger' => $logger
    ]
);

// Or set logger after initialization
$client->setLogger($logger);
```

### Log Output Example

When a retry occurs, the logger will output (at WARNING level):

```
[2024-01-15 10:30:45] ps-api.WARNING: Retrying request due to error response {
    "retry_count": 1,
    "max_retries": 3,
    "status_code": 503,
    "method": "GET",
    "uri": "https://api.psinfoodservice.nl/v7/json/MyProducts",
    "delay_ms": 1000
}

[2024-01-15 10:30:47] ps-api.WARNING: Retrying request due to error response {
    "retry_count": 2,
    "max_retries": 3,
    "status_code": 503,
    "method": "GET",
    "uri": "https://api.psinfoodservice.nl/v7/json/MyProducts",
    "delay_ms": 2000
}
```

## Retryable Conditions

### HTTP Status Codes

The following status codes trigger automatic retries:
- **500** - Internal Server Error
- **502** - Bad Gateway
- **503** - Service Unavailable
- **504** - Gateway Timeout

### Network Exceptions

The following exceptions trigger automatic retries:
- **Connection timeouts** - Server didn't respond in time
- **Connection refused** - Couldn't establish connection
- **Network unreachable** - Network connectivity issues

### Non-Retryable Errors

The following errors are **NOT** retried (fail immediately):
- **4xx errors** (except rate limiting)
  - 400 Bad Request
  - 401 Unauthorized
  - 403 Forbidden
  - 404 Not Found
- **Client-side errors** (invalid data, malformed requests)
- **Authentication failures**

## Advanced Scenarios

### Long-Running Batch Jobs

For batch processing, retries help ensure reliability:

```php
$client = new PSinfoodserviceClient(
    Environment::production,
    null,
    true,
    true,
    [
        'enabled' => true,
        'max_retries' => 5,     // More retries for batch jobs
        'retry_delay' => 3000    // Longer delays (3s, 6s, 12s, 24s, 48s)
    ]
);

$client->authentication->login('username', 'password');

foreach ($largeDataset as $item) {
    try {
        // Automatic retries on temporary failures
        $response = $client->webApi->updateProductSheet($item);
        echo "Success: {$item['id']}\n";
    } catch (PSApiException $e) {
        // Only fails after all retries are exhausted
        echo "Failed after retries: {$item['id']} - {$e->getMessage()}\n";
        // Log failure for manual review
    }
}
```

### Combining with Token Refresh

Retry logic works seamlessly with automatic token refresh:

```php
$client = new PSinfoodserviceClient(
    Environment::production,
    null,
    true,   // SSL verification
    true,   // Auto token refresh
    ['enabled' => true]  // Auto retry
);

$client->authentication->login('username', 'password');

// Even if token expires mid-process AND server has temporary issues:
// 1. Token will be automatically refreshed
// 2. Request will be retried on temporary failures
$products = $client->webApi->getMyProducts();
```

### Custom Retry Logic

For complete control, you can disable built-in retries and implement your own:

```php
$client = new PSinfoodserviceClient(
    Environment::production,
    null,
    true,
    true,
    ['enabled' => false]  // Disable built-in retry
);

$client->authentication->login('username', 'password');

// Custom retry logic
$maxAttempts = 3;
$attempt = 0;

while ($attempt < $maxAttempts) {
    try {
        $products = $client->webApi->getMyProducts();
        break;  // Success!
    } catch (PSApiException $e) {
        $attempt++;

        if ($attempt >= $maxAttempts) {
            throw $e;  // All attempts failed
        }

        // Custom logic: only retry on specific errors
        if ($e->getStatusCode() === 503) {
            echo "Service unavailable, retrying in 5 seconds...\n";
            sleep(5);
        } else {
            throw $e;  // Don't retry other errors
        }
    }
}
```

## Performance Considerations

### Retry Impact on Response Time

With 3 retries and exponential backoff (1s, 2s, 4s):
- **Successful 1st try**: Normal response time (~200ms)
- **Success on 2nd try**: +1 second
- **Success on 3rd try**: +3 seconds (1s + 2s)
- **Success on 4th try**: +7 seconds (1s + 2s + 4s)
- **All retries fail**: +7 seconds before exception

### Optimizing for Your Use Case

**Low Latency Applications** (interactive web apps):
```php
['max_retries' => 2, 'retry_delay' => 500]  // Quick retries: 500ms, 1s
```

**Batch Processing** (background jobs):
```php
['max_retries' => 5, 'retry_delay' => 2000]  // Patient retries: 2s, 4s, 8s, 16s, 32s
```

**Critical Operations** (financial transactions):
```php
['enabled' => false]  // No automatic retries, manual error handling
```

## Testing

### Testing Retry Behavior

```php
class RetryTest extends TestCase
{
    public function testRetryOnServerError()
    {
        $client = new PSinfoodserviceClient(
            Environment::preproduction,
            null,
            false,  // Disable SSL for testing
            false,  // Disable auto-refresh for predictable behavior
            [
                'enabled' => true,
                'max_retries' => 2,
                'retry_delay' => 100  // Fast retries for testing
            ]
        );

        // Mock server responses
        // ... test implementation
    }

    public function testNoRetryOnClientError()
    {
        $client = new PSinfoodserviceClient(
            Environment::preproduction,
            null,
            false,
            false,
            ['enabled' => true, 'max_retries' => 3]
        );

        // 400 errors should NOT be retried
        // ... test implementation
    }
}
```

### Disable Retries in Tests

For faster, more predictable tests:

```php
$client = new PSinfoodserviceClient(
    Environment::preproduction,
    null,
    false,
    false,
    ['enabled' => false]  // No retries in tests
);
```

## Troubleshooting

### Requests Taking Too Long

**Problem**: API calls are taking much longer than expected

**Possible causes**:
1. Retries are happening due to server issues
2. Too many retries configured
3. Delay is too long

**Debug**:
```php
// Add logger to see retry attempts
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('debug');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

$client->setLogger($logger);

// Make request and watch for retry logs
$products = $client->webApi->getMyProducts();
```

**Solutions**:
```php
// Reduce retries
$client->setMaxRetries(1);

// Reduce delay
$client->setRetryDelay(500);

// Or disable retries entirely
$client->setRetryEnabled(false);
```

### Requests Still Failing After Retries

**Problem**: Getting exceptions even with retries enabled

**Possible causes**:
1. Server is completely down (not just temporary)
2. Client error (4xx) - not retryable
3. Authentication issue

**Check**:
```php
use PSinfoodservice\Exceptions\PSApiException;

try {
    $products = $client->webApi->getMyProducts();
} catch (PSApiException $e) {
    echo "Status Code: " . $e->getStatusCode() . "\n";
    echo "Message: " . $e->getMessage() . "\n";

    if ($e->getStatusCode() >= 400 && $e->getStatusCode() < 500) {
        echo "Client error - not retryable\n";
        // Fix client-side issue (auth, invalid data, etc.)
    } else {
        echo "Server error - retries were attempted\n";
        // Server may be down, try again later
    }
}
```

### Too Many Retries Overwhelming Server

**Problem**: Concerned about overwhelming server with retries

**Solution**: The exponential backoff is specifically designed to prevent this. Each retry waits progressively longer:
- If 1000 clients all fail at once
- 1st retry: All wait 1 second (spread over 1s)
- 2nd retry: All wait 2 seconds (spread over 2s)
- 3rd retry: All wait 4 seconds (spread over 4s)

This naturally distributes the retry load.

If still concerned:
```php
// Reduce max retries
$client->setMaxRetries(2);

// Increase delay to spread load more
$client->setRetryDelay(2000);
```

## Best Practices

1. **Keep Retries Enabled**: Unless you have specific requirements, keep automatic retries enabled for resilience

2. **Use Logging in Production**: Always use a logger in production to track retry patterns and identify server issues

3. **Monitor Retry Rates**: High retry rates indicate server problems that need investigation

4. **Don't Retry Forever**: Limit retries to avoid infinite loops (default 3 is good for most cases)

5. **Match Retries to Use Case**:
   - Interactive apps: 1-2 retries with short delays
   - Background jobs: 3-5 retries with longer delays
   - Critical operations: Consider manual error handling

6. **Combine with Circuit Breaker**: For high-volume applications, consider implementing a circuit breaker pattern on top of retries

7. **Test Failure Scenarios**: Always test how your application handles retry exhaustion

## Summary

Retry logic makes the SDK more resilient to temporary failures:

- ✅ Automatic retries for server errors and network issues
- ✅ Exponential backoff prevents server overload
- ✅ Configurable retry counts and delays
- ✅ PSR-3 logger integration for monitoring
- ✅ Per-request retry control
- ✅ Works seamlessly with automatic token refresh
- ✅ Production-ready defaults

Enable it and enjoy more reliable API communication!
