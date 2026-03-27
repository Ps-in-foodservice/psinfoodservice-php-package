# File and Document Handling

The PS in Foodservice PHP SDK provides comprehensive file handling capabilities through the `FileService`. This service allows you to retrieve file metadata, generate security tokens, and download images and documents.

## Overview

The FileService provides access to:
- **File Metadata** - Get detailed information about files (type, dimensions, description)
- **Security Tokens** - Generate access tokens for downloading files
- **Images** - Download product images with automatic resizing
- **Documents** - Download PDFs, Word documents, Excel files, etc.

All file operations support rate limiting and automatic retry logic.

## Basic Usage

### Accessing the FileService

The FileService is available through the main client:

```php
use PSinfoodservice\PSinfoodserviceClient;
use PSinfoodservice\Domain\Environment;

$client = new PSinfoodserviceClient(Environment::production);
$client->authentication->login('username', 'password');

// Access file operations
$client->files->getFile(12345);
$client->files->getSecurityToken(12345);
$client->files->getImage(12345, $securityToken);
$client->files->getDocument(67890, $securityToken);
```

## File Metadata

### Get File Information

Retrieve comprehensive metadata about a file:

```php
use PSinfoodservice\Exceptions\PSApiException;

try {
    $fileMetadata = $client->files->getFile(12345);

    if ($fileMetadata) {
        echo "File ID: {$fileMetadata->FileId}\n";
        echo "File Name: {$fileMetadata->FileFullName}\n";
        echo "Description: {$fileMetadata->Description}\n";
        echo "File Type: {$fileMetadata->FileType->Name}\n";
        echo "URL: {$fileMetadata->Url}\n";

        // Check if it's an image
        if ($fileMetadata->PixelWidth > 0) {
            echo "Image Dimensions: {$fileMetadata->PixelWidth}x{$fileMetadata->PixelHeight}\n";
            echo "High Quality: " . ($fileMetadata->IsHighQuality ? 'Yes' : 'No') . "\n";
            echo "Has Transparency: " . ($fileMetadata->HasTransparency ? 'Yes' : 'No') . "\n";
        }
    }
} catch (PSApiException $e) {
    echo "Error: {$e->getMessage()}\n";
}
```

### FileContentDto Properties

The `FileContentDto` contains:

| Property | Type | Description |
|----------|------|-------------|
| `FileId` | int | Unique file identifier |
| `FileType` | FileTypeDto | File type information |
| `FileUsageType` | FileUsageTypeDto | Usage type (product image, document, etc.) |
| `FileName` | string | File name without extension |
| `FileExtension` | string | File extension (e.g., "jpg", "pdf") |
| `FileFullName` | string | Full file name with extension |
| `FriendlyName` | string | User-friendly display name |
| `Description` | string | File description |
| `SecurityToken` | string | GUID token for accessing the file |
| `PixelWidth` | int | Image width in pixels (0 if not an image) |
| `PixelHeight` | int | Image height in pixels (0 if not an image) |
| `Url` | string | Full URL to access the file |
| `IsHighQuality` | bool | Whether this is a high quality image |
| `HasTransparency` | bool | Whether the image has transparency |

## Security Tokens

### Generate Access Token

Before downloading a file, you need a security token:

```php
try {
    $tokenInfo = $client->files->getSecurityToken(12345);

    if ($tokenInfo) {
        echo "File ID: {$tokenInfo->FileId}\n";
        echo "Security Token: {$tokenInfo->SecurityToken}\n";
        echo "Direct URL: {$tokenInfo->Url}\n";

        // Token is valid for a limited time
        // Use it immediately to download the file
    }
} catch (PSApiException $e) {
    echo "Error: {$e->getMessage()}\n";
}
```

### FileSecurityTokenDto Properties

| Property | Type | Description |
|----------|------|-------------|
| `FileId` | int | File identifier |
| `SecurityToken` | string | GUID token for access |
| `Url` | string | Direct URL with token included |

**Important:** Security tokens expire after a certain period. Generate a new token if downloads fail.

## Image Handling

### Download Product Images

Download images with automatic resizing:

```php
try {
    // Step 1: Get security token
    $tokenInfo = $client->files->getSecurityToken(12345);

    // Step 2: Download image with desired dimensions
    $imageData = $client->files->getImage(
        $tokenInfo->FileId,
        $tokenInfo->SecurityToken,
        800,  // width
        600   // height
    );

    if ($imageData) {
        // Save to file
        file_put_contents('product.jpg', $imageData);
        echo "Image saved successfully!\n";
    }
} catch (PSApiException $e) {
    echo "Error: {$e->getMessage()}\n";
}
```

### Image Resizing

Images are automatically resized while maintaining aspect ratio:

```php
// Original: 2000x1500 pixels

// Request 800x600
$imageData = $client->files->getImage($fileId, $token, 800, 600);
// Result: 800x600 (fits within bounds, maintains ratio)

// Request 500x500
$imageData = $client->files->getImage($fileId, $token, 500, 500);
// Result: 500x375 (maintains original 4:3 ratio)

// Default dimensions
$imageData = $client->files->getImage($fileId, $token);
// Result: 500x500 (default size)
```

### Display Image in Browser

Serve images directly to web browsers:

```php
try {
    $tokenInfo = $client->files->getSecurityToken($_GET['fileId']);
    $imageData = $client->files->getImage(
        $tokenInfo->FileId,
        $tokenInfo->SecurityToken,
        800,
        600
    );

    if ($imageData) {
        header('Content-Type: image/jpeg');
        header('Content-Length: ' . strlen($imageData));
        echo $imageData;
    }
} catch (PSApiException $e) {
    http_response_code(500);
    echo "Error loading image";
}
```

## Document Handling

### Download Documents

Download PDFs, Word documents, Excel files, and more:

```php
try {
    // Step 1: Get security token
    $tokenInfo = $client->files->getSecurityToken(67890);

    // Step 2: Download document
    $document = $client->files->getDocument(
        $tokenInfo->FileId,
        $tokenInfo->SecurityToken
    );

    if ($document) {
        echo "Content Type: {$document['contentType']}\n";
        echo "File Name: {$document['fileName']}\n";
        echo "Size: " . strlen($document['data']) . " bytes\n";

        // Save to file
        file_put_contents($document['fileName'], $document['data']);
        echo "Document saved successfully!\n";
    }
} catch (PSApiException $e) {
    echo "Error: {$e->getMessage()}\n";
}
```

### Document Return Format

The `getDocument()` method returns an array:

```php
[
    'data' => '...',           // Raw file bytes
    'contentType' => '...',    // MIME type (e.g., 'application/pdf')
    'fileName' => '...'        // Suggested filename
]
```

### Serve Document Downloads

Serve documents as browser downloads:

```php
try {
    $tokenInfo = $client->files->getSecurityToken($_GET['fileId']);
    $document = $client->files->getDocument(
        $tokenInfo->FileId,
        $tokenInfo->SecurityToken
    );

    if ($document) {
        header("Content-Type: {$document['contentType']}");
        header("Content-Disposition: attachment; filename=\"{$document['fileName']}\"");
        header('Content-Length: ' . strlen($document['data']));
        echo $document['data'];
    }
} catch (PSApiException $e) {
    http_response_code(500);
    echo "Error downloading document";
}
```

### Supported Document Types

Common document formats:

| Format | Extension | Content Type |
|--------|-----------|--------------|
| PDF | .pdf | application/pdf |
| Word (Legacy) | .doc | application/msword |
| Word (Modern) | .docx | application/vnd.openxmlformats-officedocument.wordprocessingml.document |
| Excel (Legacy) | .xls | application/vnd.ms-excel |
| Excel (Modern) | .xlsx | application/vnd.openxmlformats-officedocument.spreadsheetml.sheet |
| PowerPoint (Legacy) | .ppt | application/vnd.ms-powerpoint |
| PowerPoint (Modern) | .pptx | application/vnd.openxmlformats-officedocument.presentationml.presentation |
| Text | .txt | text/plain |
| CSV | .csv | text/csv |

## Advanced Scenarios

### Batch Image Download

Download multiple product images efficiently:

```php
$productImages = [12345, 12346, 12347, 12348];
$downloadedImages = [];

foreach ($productImages as $fileId) {
    try {
        // Get token
        $tokenInfo = $client->files->getSecurityToken($fileId);

        // Download thumbnail (smaller for preview)
        $imageData = $client->files->getImage(
            $tokenInfo->FileId,
            $tokenInfo->SecurityToken,
            200,  // thumbnail width
            200   // thumbnail height
        );

        if ($imageData) {
            $filename = "image_{$fileId}.jpg";
            file_put_contents("images/{$filename}", $imageData);
            $downloadedImages[] = $filename;
        }

        // Add small delay to respect rate limits
        usleep(100000);  // 100ms = 10 requests/second

    } catch (PSApiException $e) {
        echo "Failed to download image {$fileId}: {$e->getMessage()}\n";
    }
}

echo "Downloaded " . count($downloadedImages) . " images\n";
```

### Caching File Metadata

Cache file metadata to reduce API calls:

```php
class FileMetadataCache
{
    private array $cache = [];
    private PSinfoodserviceClient $client;

    public function __construct(PSinfoodserviceClient $client)
    {
        $this->client = $client;
    }

    public function getFile(int $fileId): ?FileContentDto
    {
        // Check cache first
        if (isset($this->cache[$fileId])) {
            return $this->cache[$fileId];
        }

        // Fetch from API
        $metadata = $this->client->files->getFile($fileId);

        // Cache result
        if ($metadata) {
            $this->cache[$fileId] = $metadata;
        }

        return $metadata;
    }
}

// Usage
$cache = new FileMetadataCache($client);

// First call hits API
$file1 = $cache->getFile(12345);

// Second call uses cache
$file2 = $cache->getFile(12345);  // No API call
```

### Dynamic Image Sizing

Serve images at different sizes based on device:

```php
function getOptimalImageSize(string $deviceType): array
{
    switch ($deviceType) {
        case 'mobile':
            return ['width' => 400, 'height' => 400];
        case 'tablet':
            return ['width' => 800, 'height' => 800];
        case 'desktop':
            return ['width' => 1200, 'height' => 1200];
        default:
            return ['width' => 500, 'height' => 500];
    }
}

try {
    $deviceType = $_GET['device'] ?? 'desktop';
    $size = getOptimalImageSize($deviceType);

    $tokenInfo = $client->files->getSecurityToken($fileId);
    $imageData = $client->files->getImage(
        $tokenInfo->FileId,
        $tokenInfo->SecurityToken,
        $size['width'],
        $size['height']
    );

    header('Content-Type: image/jpeg');
    echo $imageData;
} catch (PSApiException $e) {
    // Serve placeholder image
    readfile('placeholder.jpg');
}
```

### Progressive Image Loading

Implement progressive image loading (low-res → high-res):

```php
try {
    $tokenInfo = $client->files->getSecurityToken($fileId);

    // Step 1: Load low-res preview (fast)
    $thumbnail = $client->files->getImage(
        $tokenInfo->FileId,
        $tokenInfo->SecurityToken,
        100,  // very small
        100
    );
    file_put_contents("preview_{$fileId}.jpg", $thumbnail);

    // Step 2: Load medium-res (for display)
    $mediumRes = $client->files->getImage(
        $tokenInfo->FileId,
        $tokenInfo->SecurityToken,
        800,
        600
    );
    file_put_contents("display_{$fileId}.jpg", $mediumRes);

    // Step 3: Load high-res (for zoom/detail)
    $highRes = $client->files->getImage(
        $tokenInfo->FileId,
        $tokenInfo->SecurityToken,
        2000,
        1500
    );
    file_put_contents("full_{$fileId}.jpg", $highRes);

} catch (PSApiException $e) {
    echo "Error: {$e->getMessage()}\n";
}
```

## Rate Limiting

All file endpoints have rate limits:

| Endpoint | Rate Limit |
|----------|------------|
| `getFile()` | 10 requests/second |
| `getSecurityToken()` | 10 requests/second |
| `getImage()` | 15 requests/second |
| `getDocument()` | 15 requests/second |

### Handling Rate Limits

Enable automatic rate limit handling:

```php
use PSinfoodservice\Exceptions\RateLimitException;

// Option 1: Enable automatic wait-and-retry
$client = new PSinfoodserviceClient(
    Environment::production,
    null,
    true,
    true,
    ['rate_limit_auto_wait' => true, 'rate_limit_max_wait' => 60]
);

// Option 2: Handle manually
try {
    $imageData = $client->files->getImage($fileId, $token);
} catch (RateLimitException $e) {
    $waitSeconds = $e->getRetryAfter();
    echo "Rate limited. Waiting {$waitSeconds} seconds...\n";
    sleep($waitSeconds);
    $imageData = $client->files->getImage($fileId, $token);
}
```

## Error Handling

### Common Errors

```php
use PSinfoodservice\Exceptions\PSApiException;
use PSinfoodservice\Exceptions\RateLimitException;

try {
    $fileMetadata = $client->files->getFile($fileId);
} catch (RateLimitException $e) {
    // Rate limit exceeded
    echo "Rate limit: {$e->getUserMessage()}\n";
    // Wait and retry
} catch (PSApiException $e) {
    switch ($e->getStatusCode()) {
        case 400:
            echo "Invalid file ID\n";
            break;
        case 401:
            echo "Not authenticated\n";
            break;
        case 403:
            echo "No permission to access file\n";
            break;
        case 404:
            echo "File not found\n";
            break;
        case 500:
            echo "Server error\n";
            break;
        default:
            echo "Error: {$e->getMessage()}\n";
    }
}
```

### Validation

The SDK validates parameters before making API calls:

```php
try {
    // These will throw PSApiException with 400 status code
    $client->files->getFile(0);           // File ID must be > 0
    $client->files->getImage($id, $token, 0, 500);    // Width must be > 0
    $client->files->getImage($id, $token, 500, -100); // Height must be > 0
} catch (PSApiException $e) {
    echo "Validation error: {$e->getMessage()}\n";
}
```

## Best Practices

1. **Cache Security Tokens**: Tokens are valid for a period - cache them to reduce API calls

2. **Respect Rate Limits**: Add delays between requests in batch operations

3. **Use Appropriate Image Sizes**: Request only the size you need to save bandwidth

4. **Handle Missing Files Gracefully**: Not all products have images/documents

5. **Validate File IDs**: Check that file IDs are valid before making requests

6. **Use Typed DTOs**: Leverage the typed response DTOs for better IDE support

7. **Implement Retry Logic**: Enable automatic retries for resilient file downloads

8. **Monitor Download Sizes**: Track bandwidth usage for large file downloads

## Complete Example

Full workflow for displaying product images:

```php
<?php
require_once 'vendor/autoload.php';

use PSinfoodservice\PSinfoodserviceClient;
use PSinfoodservice\Domain\Environment;
use PSinfoodservice\Exceptions\PSApiException;
use PSinfoodservice\Exceptions\RateLimitException;

// Initialize client
$client = new PSinfoodserviceClient(
    Environment::production,
    null,
    true,
    true,
    ['rate_limit_auto_wait' => true]
);

// Login
$client->authentication->login('username', 'password');

// Get product and find main image
$productSheet = $client->webApi->getProductSheet(12345);

if ($productSheet && isset($productSheet->Logistic->ImageList)) {
    foreach ($productSheet->Logistic->ImageList as $imageInfo) {
        try {
            // Get file metadata
            $fileMetadata = $client->files->getFile($imageInfo->FileId);

            if ($fileMetadata && $fileMetadata->FileType->Name === 'Image') {
                echo "Processing: {$fileMetadata->FriendlyName}\n";

                // Get security token
                $tokenInfo = $client->files->getSecurityToken($fileMetadata->FileId);

                // Download multiple sizes
                $sizes = [
                    'thumb' => ['width' => 200, 'height' => 200],
                    'medium' => ['width' => 800, 'height' => 600],
                    'large' => ['width' => 1600, 'height' => 1200]
                ];

                foreach ($sizes as $sizeName => $dimensions) {
                    $imageData = $client->files->getImage(
                        $tokenInfo->FileId,
                        $tokenInfo->SecurityToken,
                        $dimensions['width'],
                        $dimensions['height']
                    );

                    if ($imageData) {
                        $filename = "product_{$fileMetadata->FileId}_{$sizeName}.jpg";
                        file_put_contents("images/{$filename}", $imageData);
                        echo "  - Saved {$sizeName}: {$filename}\n";
                    }
                }
            }
        } catch (RateLimitException $e) {
            echo "Rate limited: {$e->getUserMessage()}\n";
            break;
        } catch (PSApiException $e) {
            echo "Error: {$e->getMessage()}\n";
        }
    }
}
```

## Summary

The FileService provides comprehensive file handling:

- ✅ Retrieve detailed file metadata with `getFile()`
- ✅ Generate security tokens with `getSecurityToken()`
- ✅ Download images with automatic resizing via `getImage()`
- ✅ Download documents of any type via `getDocument()`
- ✅ Full rate limit handling and automatic retries
- ✅ Typed response DTOs for better IDE support
- ✅ Comprehensive error handling
- ✅ Support for batch operations

For more information, see the API documentation and inline method examples.
