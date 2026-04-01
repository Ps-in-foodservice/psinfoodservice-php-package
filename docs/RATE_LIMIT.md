# Rate Limit Handling

The PS in Foodservice PHP SDK includes comprehensive rate limit handling to help you manage API rate limits gracefully and comply with usage policies.

## Overview

API rate limits prevent abuse and ensure fair usage for all users. When you exceed the rate limit, the API returns:
- **HTTP Status 429** - Too Many Requests
- **Retry-After header** - Indicates when you can retry (in seconds or as HTTP date)
- **Optional rate limit information** - Current limit (e.g., "5 requests/second")

The SDK provides two approaches to handling rate limits:
1. **Manual handling** - Catch RateLimitException and handle yourself (default)
2. **Automatic handling** - SDK automatically waits and retries (optional)

## Default Behavior

By default, rate limit **automatic handling is disabled**. When you hit a rate limit:
- A `RateLimitException` is thrown
- Exception contains retry information (seconds to wait, endpoint, rate limit)
- You must handle the exception manually

This gives you full control over rate limit handling in your application.

## Basic Usage

### Manual Handling (Default)

Handle rate limits manually by catching the exception:

```php
use PSinfoodservice\PSinfoodserviceClient;
use PSinfoodservice\Domain\Environment;
use PSinfoodservice\Exceptions\RateLimitException;

$client = new PSinfoodserviceClient(Environment::production);
$client->authentication->login('username', 'password');

try {
    $products = $client->webApi->getMyProducts();
} catch (RateLimitException $e) {
    // Rate limit exceeded
    $waitSeconds = $e->getRetryAfter();    // Seconds to wait
    $endpoint = $e->getEndpoint();         // The endpoint that was hit
    $rateLimit = $e->getRateLimit();       // Rate limit (e.g., 5 req/sec)

    echo "Rate limit exceeded for {$endpoint}\n";
    echo "Limit: {$rateLimit} requests/second\n";
    echo "Retry after: {$waitSeconds} seconds\n";

    // Wait and retry
    sleep($waitSeconds);
    $products = $client->webApi->getMyProducts();
}
```

### Automatic Handling

Enable automatic wait-and-retry for seamless operation:

```php
$client = new PSinfoodserviceClient(
    Environment::production,
    null,  // apiPrefix
    true,  // verifySSL
    true,  // autoRefreshEnabled
    [      // retryConfig
        'rate_limit_auto_wait' => true,     // Enable auto-wait
        'rate_limit_max_wait' => 60         // Max 60 seconds wait
    ]
);

$client->authentication->login('username', 'password');

// If rate limited, SDK automatically waits and retries
// No exception thrown if wait time <= 60 seconds
$products = $client->webApi->getMyProducts();
```

## Configuration Options

### Constructor Configuration

```php
$client = new PSinfoodserviceClient(
    Environment::production,
    null,
    true,
    true,
    [
        'rate_limit_auto_wait' => true,   // Auto-wait on rate limit
        'rate_limit_max_wait' => 120,     // Max wait time: 2 minutes
        'logger' => $logger               // PSR-3 logger for monitoring
    ]
);
```

### Runtime Configuration

Change settings after client initialization:

```php
// Enable automatic wait-and-retry
$client->setRateLimitAutoWait(true);

// Set maximum wait time (seconds)
$client->setRateLimitMaxWait(90);

// Get current settings
$autoWait = $client->isRateLimitAutoWaitEnabled();  // true
$maxWait = $client->getRateLimitMaxWait();          // 90
```

### Per-Request Configuration

Override auto-wait for specific requests (advanced):

```php
// Disable auto-wait for this request only
$response = $client->getHttpClient()->get(
    $client->buildApiPath('MyProducts'),
    ['rate_limit_auto_wait' => false]
);

// This will throw RateLimitException even if auto-wait is globally enabled
```

## Rate Limit Information

### RateLimitException Properties

The exception provides comprehensive rate limit information:

```php
try {
    $products = $client->webApi->getMyProducts();
} catch (RateLimitException $e) {
    // Core information
    echo $e->getMessage();           // "Rate limit exceeded"
    echo $e->getStatusCode();        // 429

    // Rate limit details
    echo $e->getRetryAfter();        // Seconds to wait (e.g., 3)
    echo $e->getEndpoint();          // Endpoint URL
    echo $e->getRateLimit();         // Rate limit (e.g., 5)
    echo $e->getTraceId();           // API trace ID for debugging

    // User-friendly message
    echo $e->getUserMessage();
    // Output: "Rate limit exceeded. Limit: 5 requests/second.
    //          Endpoint: https://api.psinfoodservice.nl/v7/json/MyProducts.
    //          Please retry after 3 second(s)."
}
```

## Automatic Handling Details

### How Auto-Wait Works

When auto-wait is enabled and you hit a rate limit:

1. **SDK detects 429 response** with Retry-After header
2. **Checks if wait time is acceptable** (≤ maxWaitTime)
3. **Waits the specified duration** using `sleep()`
4. **Automatically retries** the request
5. **Returns the successful response** as if nothing happened

If wait time exceeds maxWaitTime, a `RateLimitException` is thrown.

### Wait Time Protection

The `rate_limit_max_wait` setting protects against excessively long waits:

```php
// Rate limit says: wait 120 seconds
// But max wait is set to 60 seconds

$client->setRateLimitMaxWait(60);

try {
    $products = $client->webApi->getMyProducts();
} catch (RateLimitException $e) {
    // Exception thrown because 120s > 60s max wait
    echo "Wait time too long: " . $e->getRetryAfter() . " seconds\n";
}
```

This prevents your application from hanging on long rate limit penalties.

## Logging

### PSR-3 Logger Integration

Monitor rate limit handling with a logger:

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('ps-api');
$logger->pushHandler(new StreamHandler('path/to/api.log', Logger::WARNING));

$client = new PSinfoodserviceClient(
    Environment::production,
    null,
    true,
    true,
    [
        'rate_limit_auto_wait' => true,
        'logger' => $logger
    ]
);
```

### Log Output

When a rate limit is encountered:

**Without auto-wait** (WARNING level):
```
[2024-01-15 10:30:45] ps-api.WARNING: Rate limit exceeded {
    "endpoint": "https://api.psinfoodservice.nl/v7/json/MyProducts",
    "retry_after": 5,
    "auto_wait": false
}
```

**With auto-wait** (INFO level):
```
[2024-01-15 10:30:45] ps-api.WARNING: Rate limit exceeded {
    "endpoint": "https://api.psinfoodservice.nl/v7/json/productsheet",
    "retry_after": 3,
    "auto_wait": true
}

[2024-01-15 10:30:45] ps-api.INFO: Automatically waiting for rate limit {
    "wait_seconds": 3,
    "endpoint": "https://api.psinfoodservice.nl/v7/json/productsheet"
}

[2024-01-15 10:30:48] ps-api.INFO: Retrying after rate limit wait {
    "endpoint": "https://api.psinfoodservice.nl/v7/json/productsheet"
}
```

## API Endpoint Rate Limits

Different endpoints have different rate limits:

| Endpoint | Rate Limit | Notes |
|----------|------------|-------|
| `PUT /productsheet` | 5 requests/second | Product updates |
| `GET /MyProducts` | Standard | Product listing |
| `GET /ProductSheet/{id}` | Standard | Individual product |
| Most other endpoints | Standard | General operations |

**Standard rate limit** is enforced per user account across all endpoints.

## Advanced Scenarios

### Batch Processing with Rate Limits

Handle rate limits in batch operations:

```php
$client->setRateLimitAutoWait(true);
$client->setRateLimitMaxWait(120);  // Allow longer waits for batch jobs

$products = [...];  // Large dataset
$processed = 0;
$rateLimited = 0;

foreach ($products as $product) {
    try {
        $result = $client->webApi->updateProductSheet($product);
        $processed++;

        // Optional: Add small delay to avoid hitting limit
        usleep(200000);  // 200ms delay between requests

    } catch (RateLimitException $e) {
        // Wait time exceeded max wait
        $rateLimited++;
        echo "Rate limit wait too long, skipping: {$product['id']}\n";

        // Or handle manually
        sleep($e->getRetryAfter());
        $result = $client->webApi->updateProductSheet($product);
        $processed++;
    }
}

echo "Processed: {$processed}, Rate limited: {$rateLimited}\n";
```

### Proactive Rate Limiting

Implement your own rate limiting to avoid hitting API limits:

```php
class RateLimiter
{
    private int $requestsPerSecond;
    private float $lastRequestTime;

    public function __construct(int $requestsPerSecond = 5)
    {
        $this->requestsPerSecond = $requestsPerSecond;
        $this->lastRequestTime = 0;
    }

    public function throttle(): void
    {
        $minInterval = 1.0 / $this->requestsPerSecond;  // 0.2s for 5 req/s
        $elapsed = microtime(true) - $this->lastRequestTime;

        if ($elapsed < $minInterval) {
            $sleepTime = ($minInterval - $elapsed) * 1000000;  // microseconds
            usleep((int)$sleepTime);
        }

        $this->lastRequestTime = microtime(true);
    }
}

// Usage
$rateLimiter = new RateLimiter(5);  // 5 requests/second
$client = new PSinfoodserviceClient(Environment::production);
$client->authentication->login('username', 'password');

foreach ($products as $product) {
    $rateLimiter->throttle();  // Prevent hitting rate limit
    $result = $client->webApi->updateProductSheet($product);
}
```

### Combining with Retry Logic

Rate limit handling works alongside retry logic:

```php
$client = new PSinfoodserviceClient(
    Environment::production,
    null,
    true,
    true,
    [
        // Retry logic for server errors
        'enabled' => true,
        'max_retries' => 3,
        'retry_delay' => 1000,

        // Rate limit handling
        'rate_limit_auto_wait' => true,
        'rate_limit_max_wait' => 60,

        // Shared logger
        'logger' => $logger
    ]
);

// Request flow:
// 1. Rate limit middleware checks for 429
// 2. If not rate limited, retry middleware checks for 5xx
// 3. Both can auto-handle or throw exceptions
```

## Best Practices

1. **Use Auto-Wait for Background Jobs**: Enable automatic handling for batch processing and background tasks where latency is acceptable.

2. **Use Manual Handling for Interactive Apps**: Disable auto-wait for web applications to maintain control over user experience.

3. **Set Appropriate Max Wait Times**:
   - Interactive apps: 5-10 seconds
   - Background jobs: 60-120 seconds
   - Critical operations: Disable auto-wait

4. **Implement Proactive Rate Limiting**: Add delays between requests in batch operations to avoid hitting limits.

5. **Monitor Rate Limits**: Use logging to track when and where you hit rate limits.

6. **Respect the Retry-After Header**: Always wait the full duration specified by the API.

7. **Handle Edge Cases**: Plan for scenarios where auto-wait is disabled or max wait is exceeded.

## Error Handling

### Comprehensive Error Handling

Handle all possible exceptions:

```php
use PSinfoodservice\Exceptions\RateLimitException;
use PSinfoodservice\Exceptions\PSApiException;

try {
    $products = $client->webApi->getMyProducts();

} catch (RateLimitException $e) {
    // Rate limit exceeded
    echo "Rate limited: " . $e->getUserMessage() . "\n";

    // Decide whether to retry
    if ($e->getRetryAfter() <= 10) {
        sleep($e->getRetryAfter());
        $products = $client->webApi->getMyProducts();
    } else {
        throw $e;  // Wait time too long, propagate error
    }

} catch (PSApiException $e) {
    // Other API errors (auth, validation, server errors)
    echo "API Error: " . $e->getMessage() . "\n";
    echo "Status: " . $e->getStatusCode() . "\n";
}
```

## Testing

### Testing Rate Limit Handling

```php
class RateLimitTest extends TestCase
{
    public function testManualRateLimitHandling()
    {
        $client = new PSinfoodserviceClient(
            Environment::preproduction,
            null,
            false,  // Disable SSL for testing
            false,  // Disable auto-refresh
            ['rate_limit_auto_wait' => false]  // Manual handling
        );

        // Mock or trigger rate limit...
        // Assert that RateLimitException is thrown
        // Assert correct retry information
    }

    public function testAutoWaitRateLimitHandling()
    {
        $client = new PSinfoodserviceClient(
            Environment::preproduction,
            null,
            false,
            false,
            [
                'rate_limit_auto_wait' => true,
                'rate_limit_max_wait' => 10
            ]
        );

        // Mock or trigger rate limit with short wait time
        // Assert that request succeeds after auto-wait
    }
}
```

## Troubleshooting

### Frequent Rate Limiting

**Problem**: Constantly hitting rate limits

**Solutions**:
1. Implement proactive rate limiting (see example above)
2. Reduce request frequency in batch operations
3. Use pagination to reduce data per request
4. Cache frequently accessed data locally
5. Review your usage patterns - consider if all requests are necessary

### Auto-Wait Not Working

**Problem**: Still getting RateLimitException despite auto-wait being enabled

**Possible causes**:
1. Wait time exceeds `rate_limit_max_wait`
2. Per-request override is disabling auto-wait
3. Configuration not applied correctly

**Debug**:
```php
// Check configuration
echo "Auto-wait: " . ($client->isRateLimitAutoWaitEnabled() ? 'Yes' : 'No') . "\n";
echo "Max wait: " . $client->getRateLimitMaxWait() . " seconds\n";

// Check exception details
try {
    $products = $client->webApi->getMyProducts();
} catch (RateLimitException $e) {
    echo "Retry after: " . $e->getRetryAfter() . " seconds\n";
    echo "Max wait: " . $client->getRateLimitMaxWait() . " seconds\n";

    if ($e->getRetryAfter() > $client->getRateLimitMaxWait()) {
        echo "Wait time exceeds max wait - that's why exception was thrown\n";
    }
}
```

### Unexpected Long Waits

**Problem**: Application hangs during auto-wait

**Solutions**:
1. Reduce `rate_limit_max_wait` to acceptable level
2. Disable auto-wait for time-sensitive operations
3. Use manual handling with custom logic
4. Add logging to monitor wait times

## Summary

Rate limit handling in the SDK provides:

- ✅ Automatic detection of 429 responses
- ✅ Parsing of Retry-After header (seconds and HTTP date formats)
- ✅ Optional automatic wait-and-retry
- ✅ Configurable maximum wait time
- ✅ Comprehensive exception with retry information
- ✅ PSR-3 logger integration
- ✅ Per-request configuration overrides
- ✅ User-friendly error messages
- ✅ Works seamlessly with retry logic and token refresh

Choose the approach that best fits your use case:
- **Manual handling**: Full control, best for interactive applications
- **Automatic handling**: Convenience, best for background jobs and batch processing

Respect rate limits to ensure fair API usage for all users!
