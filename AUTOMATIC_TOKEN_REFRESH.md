# Automatic Token Refresh

The PS in Foodservice PHP SDK now includes automatic token refresh functionality to prevent API calls from failing due to expired authentication tokens.

## Overview

When you authenticate with the API, you receive an access token with a limited lifetime (typically 3600 seconds / 1 hour). Previously, you had to manually track token expiry and refresh tokens before they expired. Now, the SDK handles this automatically.

## How It Works

1. **Token Tracking**: When you login or refresh a token, the SDK records the timestamp
2. **Expiry Detection**: Before the token expires, the SDK automatically detects it's about to expire (60 seconds before by default)
3. **Automatic Refresh**: The SDK automatically calls the refresh token endpoint to obtain a new token
4. **Seamless Operation**: Your API calls continue to work without interruption

## Basic Usage

### Automatic Mode (Default)

By default, automatic token refresh is enabled. You don't need to do anything special:

```php
use PSinfoodservice\PSinfoodserviceClient;
use PSinfoodservice\Domain\Environment;

$client = new PSinfoodserviceClient(Environment::production);

// Login once
$client->authentication->login('username', 'password');

// Make API calls - token will be automatically refreshed as needed
$products = $client->webApi->getMyProducts();

// Even after an hour, this still works (token refreshed automatically)
sleep(3700); // Wait longer than token lifetime
$products = $client->webApi->getMyProducts(); // Still works!
```

### Manual Control

If you prefer manual control over token refresh, you can disable automatic refresh:

```php
// Disable automatic refresh
$client = new PSinfoodserviceClient(
    Environment::production,
    null,        // apiPrefix
    true,        // verifySSL
    false        // autoRefreshEnabled
);

// Or after construction
$client->setAutoRefreshEnabled(false);

// Now you must manually check and refresh
if ($client->isTokenExpired()) {
    $accessToken = $client->getAccessToken();
    $refreshToken = $client->getRefreshToken();
    $client->authentication->refreshToken($accessToken, $refreshToken);
}

$products = $client->webApi->getMyProducts();
```

### Manual Refresh Trigger

You can also manually trigger a token refresh check:

```php
// Manually ensure token is valid
$client->ensureValidToken();

// Now make your API call
$products = $client->webApi->getMyProducts();
```

## Configuration

### Token Refresh Margin

By default, the SDK considers a token "expired" 60 seconds before its actual expiry time. This safety margin prevents race conditions where a token expires mid-request.

You can customize this margin:

```php
// Set a 2-minute safety margin
$client->setTokenRefreshMargin(120);

// Or use a very small margin (not recommended)
$client->setTokenRefreshMargin(10);
```

### Checking Configuration

```php
// Check if auto-refresh is enabled
if ($client->isAutoRefreshEnabled()) {
    echo "Auto-refresh is enabled\n";
}

// Get current safety margin
$margin = $client->getTokenRefreshMargin();
echo "Token refresh margin: {$margin} seconds\n";
```

## Token Information

### Checking Token Status

```php
// Check if token is expired or about to expire
if ($client->isTokenExpired()) {
    echo "Token needs refresh\n";
} else {
    echo "Token is still valid\n";
}

// Check with custom margin
if ($client->isTokenExpired(300)) { // 5 minutes
    echo "Token expires within 5 minutes\n";
}
```

### Getting Token Details

```php
// Get current access token
$accessToken = $client->getAccessToken();

// Get refresh token
$refreshToken = $client->getRefreshToken();

// Get token lifetime (in seconds)
$expiresIn = $client->getExpiresIn();

// Get when token was obtained (Unix timestamp)
$obtainedAt = $client->getTokenObtainedAt();

// Calculate exact expiry time
if ($obtainedAt && $expiresIn) {
    $expiresAt = $obtainedAt + $expiresIn;
    $timeUntilExpiry = $expiresAt - time();
    echo "Token expires in {$timeUntilExpiry} seconds\n";
}
```

## Error Handling

### Refresh Failures

If token refresh fails (e.g., refresh token is also expired), the SDK throws a `PSApiException`:

```php
use PSinfoodservice\Exceptions\PSApiException;

try {
    $products = $client->webApi->getMyProducts();
} catch (PSApiException $e) {
    if ($e->getStatusCode() === 401) {
        // Token refresh failed - need to login again
        echo "Session expired. Please login again.\n";
        $client->authentication->login('username', 'password');
    } else {
        echo "API Error: " . $e->getMessage();
    }
}
```

### Preventing Refresh Attempts

If you haven't logged in yet (no tokens set), calling `ensureValidToken()` will throw an exception:

```php
$client = new PSinfoodserviceClient();

try {
    $client->ensureValidToken();
} catch (PSApiException $e) {
    echo $e->getMessage();
    // "Cannot refresh token: access token or refresh token is not set. Please login first."
}
```

## Advanced Scenarios

### Long-Running Scripts

For long-running scripts (e.g., batch jobs, cron jobs), automatic refresh is crucial:

```php
$client = new PSinfoodserviceClient(Environment::production);
$client->authentication->login('username', 'password');

// Process items for many hours
foreach ($largeDataset as $item) {
    // Token is automatically refreshed as needed
    $response = $client->webApi->updateProductSheet($item);

    // Process response...
    sleep(10); // Simulate processing time
}

// No need to manually track token expiry!
```

### Multiple Clients

Each client instance tracks its own token state:

```php
$client1 = new PSinfoodserviceClient(Environment::production);
$client1->authentication->login('user1', 'pass1');

$client2 = new PSinfoodserviceClient(Environment::production);
$client2->authentication->login('user2', 'pass2');

// Each client refreshes its own token independently
$products1 = $client1->webApi->getMyProducts(); // Uses user1's token
$products2 = $client2->webApi->getMyProducts(); // Uses user2's token
```

### Disabling Refresh for Testing

In unit tests, you might want to disable automatic refresh:

```php
class MyTest extends TestCase
{
    public function testApiCall()
    {
        $client = new PSinfoodserviceClient(
            Environment::preproduction,
            null,
            false,  // Disable SSL verification for testing
            false   // Disable auto-refresh for predictable behavior
        );

        // Mock or set a test token
        $client->setAccessToken('test-token');
        $client->setExpiresIn(3600);

        // Token won't be refreshed automatically
        // Test behavior with expired token
    }
}
```

## Implementation Details

### How Expiry is Calculated

```php
// Token is considered expired when:
$currentTime >= $tokenObtainedAt + $expiresIn - $safetyMargin

// Example:
// Token obtained at: 1000 (Unix timestamp)
// Expires in: 3600 seconds
// Safety margin: 60 seconds
// Token considered expired at: 1000 + 3600 - 60 = 4540
// Actual expiry: 1000 + 3600 = 4600
```

This gives a 60-second window to refresh before actual expiry.

### Refresh Process

When `ensureValidToken()` is called (automatically or manually):

1. Check if `autoRefreshEnabled` is true (skip if false)
2. Call `isTokenExpired()` to check token status
3. If expired:
   - Verify access token and refresh token are set
   - Call `authentication->refreshToken(accessToken, refreshToken)`
   - New tokens are automatically stored via `setAccessToken()`, `setRefreshToken()`, and `setExpiresIn()`
   - New timestamp is recorded
4. If refresh fails, throw `PSApiException` with clear error message

### Thread Safety

**Note**: PHP is single-threaded per request, so thread safety is not a concern. However, if you're using async frameworks (ReactPHP, Swoole), be aware that concurrent requests might trigger multiple refresh attempts. Consider using locks or semaphores if needed.

## Best Practices

1. **Keep Auto-Refresh Enabled**: Unless you have a specific reason, keep automatic refresh enabled for hassle-free operation

2. **Handle Login Separately**: Auto-refresh only works if you've already logged in. Always call `login()` at the start of your script

3. **Use Appropriate Margins**: The default 60-second margin is good for most cases. Increase it if you make very long-running API calls

4. **Catch Exceptions**: Always wrap API calls in try-catch blocks to handle refresh failures gracefully

5. **Don't Disable for Production**: Disabling auto-refresh in production increases the risk of failed API calls

6. **Store Refresh Tokens Securely**: If you're persisting tokens between sessions, store refresh tokens securely (encrypted)

## Migration from Previous Version

If you were manually handling token refresh:

### Before (Manual Refresh)

```php
$client = new PSinfoodserviceClient();
$client->authentication->login('username', 'password');

// Manual tracking
$loginTime = time();
$tokenLifetime = 3600;

// Before each API call
if (time() >= $loginTime + $tokenLifetime - 60) {
    $client->authentication->refreshToken(
        $client->getAccessToken(),
        $client->getRefreshToken()
    );
    $loginTime = time();
}

$products = $client->webApi->getMyProducts();
```

### After (Automatic Refresh)

```php
$client = new PSinfoodserviceClient();
$client->authentication->login('username', 'password');

// Just make API calls - token refresh is automatic!
$products = $client->webApi->getMyProducts();
```

## Troubleshooting

### "Cannot refresh token" Error

**Problem**: Getting "Cannot refresh token: access token or refresh token is not set"

**Solution**: Make sure you've called `login()` before making API calls:

```php
$client->authentication->login('username', 'password');
// Now tokens are set and refresh will work
```

### Token Still Expires

**Problem**: API calls fail with 401 even with auto-refresh enabled

**Possible causes**:
1. Refresh token itself has expired (requires new login)
2. Auto-refresh is disabled
3. Network issues preventing refresh call

**Debug**:
```php
echo "Auto-refresh enabled: " . ($client->isAutoRefreshEnabled() ? 'Yes' : 'No') . "\n";
echo "Token expired: " . ($client->isTokenExpired() ? 'Yes' : 'No') . "\n";
echo "Token obtained: " . date('Y-m-d H:i:s', $client->getTokenObtainedAt()) . "\n";
echo "Expires in: " . $client->getExpiresIn() . " seconds\n";
```

### Refresh Loop

**Problem**: Token keeps refreshing on every request

**Possible causes**:
1. System clock is incorrect
2. Token lifetime is very short
3. Safety margin is too large

**Solution**: Check your safety margin and token lifetime:
```php
$margin = $client->getTokenRefreshMargin();
$lifetime = $client->getExpiresIn();

if ($margin >= $lifetime) {
    // Margin is too large!
    $client->setTokenRefreshMargin(60); // Reset to default
}
```

## Summary

Automatic token refresh makes the SDK more robust and easier to use:

- ✅ No manual token tracking needed
- ✅ Prevents API call failures due to expired tokens
- ✅ Configurable safety margins and auto-refresh behavior
- ✅ Clear error messages when refresh fails
- ✅ Backward compatible - works seamlessly with existing code

For most use cases, simply enable it and forget about token management!
