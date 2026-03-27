<?php
namespace PSinfoodservice\Services;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException;
use PSinfoodservice\Exceptions\PSApiException;
use PSinfoodservice\PSinfoodserviceClient;
use PSinfoodservice\Dtos\Outgoing\FileContentDto;
use PSinfoodservice\Dtos\Outgoing\FileSecurityTokenDto;

/**
 * Service for handling file and document operations in the PS in foodservice API.
 *
 * This service provides access to file metadata, security tokens, images, and documents.
 */
class FileService
{
    /**
     * The PS in foodservice client instance.
     */
    private PSinfoodserviceClient $client;

    /**
     * Initializes a new instance of the FileService.
     *
     * @param PSinfoodserviceClient $client The PS in foodservice client
     */
    public function __construct(PSinfoodserviceClient $client)
    {
        $this->client = $client;
    }

    /**
     * Retrieves file metadata by file ID.
     *
     * Returns comprehensive metadata about a file including type, dimensions,
     * security token, and URL information.
     *
     * **Required Role:** Read
     *
     * **Rate Limiting:** 10 requests per second
     *
     * @param int $fileId The ID of the file
     * @return FileContentDto|null The file metadata or null if not found
     * @throws PSApiException If retrieval of the file fails
     * @throws \PSinfoodservice\Exceptions\RateLimitException If rate limit is exceeded
     *
     * @example
     * ```php
     * try {
     *     $fileMetadata = $client->files->getFile(12345);
     *
     *     echo "File: {$fileMetadata->FileFullName}\n";
     *     echo "Type: {$fileMetadata->FileType->Name}\n";
     *     echo "URL: {$fileMetadata->Url}\n";
     *
     *     if ($fileMetadata->PixelWidth > 0) {
     *         echo "Image dimensions: {$fileMetadata->PixelWidth}x{$fileMetadata->PixelHeight}\n";
     *     }
     * } catch (PSApiException $e) {
     *     echo "Error: " . $e->getMessage();
     * }
     * ```
     */
    public function getFile(int $fileId): ?FileContentDto
    {
        try {
            if ($fileId <= 0) {
                throw new PSApiException('File ID must be greater than 0', 400);
            }

            $response = $this->client->getHttpClient()->get(
                $this->client->buildApiPath("File/file/{$fileId}")
            );

            $data = json_decode($response->getBody()->getContents(), true);

            if (empty($data)) {
                return null;
            }

            return FileContentDto::fromData($data);

        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();

            if ($statusCode === 404) {
                return null;
            }

            $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);
            throw new PSApiException(
                $errorResponse['detail'] ?? $errorResponse['title'] ?? 'Unknown error occurred',
                $statusCode,
                $errorResponse['traceId'] ?? null
            );
        } catch (ServerException | ConnectException $e) {
            throw new PSApiException($e->getMessage(), 500);
        }
    }

    /**
     * Retrieves a security token for accessing a file.
     *
     * The security token is required for downloading images and documents.
     * Returns the file ID, security token (GUID), and direct URL.
     *
     * **Required Role:** Read
     *
     * **Rate Limiting:** 10 requests per second
     *
     * @param int $fileId The ID of the file
     * @return FileSecurityTokenDto|null The security token information or null if not found
     * @throws PSApiException If retrieval fails
     * @throws \PSinfoodservice\Exceptions\RateLimitException If rate limit is exceeded
     *
     * @example
     * ```php
     * try {
     *     $tokenInfo = $client->files->getSecurityToken(12345);
     *
     *     echo "File ID: {$tokenInfo->FileId}\n";
     *     echo "Token: {$tokenInfo->SecurityToken}\n";
     *     echo "URL: {$tokenInfo->Url}\n";
     *
     *     // Use the token to download the file
     *     $image = $client->files->getImage($tokenInfo->FileId, $tokenInfo->SecurityToken);
     * } catch (PSApiException $e) {
     *     echo "Error: " . $e->getMessage();
     * }
     * ```
     */
    public function getSecurityToken(int $fileId): ?FileSecurityTokenDto
    {
        try {
            if ($fileId <= 0) {
                throw new PSApiException('File ID must be greater than 0', 400);
            }

            $response = $this->client->getHttpClient()->get(
                $this->client->buildApiPath("File/securitytoken/{$fileId}")
            );

            $data = json_decode($response->getBody()->getContents(), true);

            if (empty($data)) {
                return null;
            }

            return FileSecurityTokenDto::fromData($data);

        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();

            if ($statusCode === 404) {
                return null;
            }

            $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);
            throw new PSApiException(
                $errorResponse['detail'] ?? $errorResponse['title'] ?? 'Unknown error occurred',
                $statusCode,
                $errorResponse['traceId'] ?? null
            );
        } catch (ServerException | ConnectException $e) {
            throw new PSApiException($e->getMessage(), 500);
        }
    }

    /**
     * Retrieves an image from the API with specified dimensions.
     *
     * Returns the raw image data (bytes) which can be saved to a file or displayed.
     * The image will be automatically resized to the specified dimensions while
     * maintaining aspect ratio.
     *
     * **No authentication required** - Uses security token for access control
     *
     * **Rate Limiting:** 15 requests per second
     *
     * @param int $fileId The ID of the image file
     * @param string $securityToken The security token for accessing the image
     * @param int $width The desired width of the image (default: 500)
     * @param int $height The desired height of the image (default: 500)
     * @return string|null The image data as bytes or null if no data is available
     * @throws PSApiException If retrieval of the image fails
     * @throws \PSinfoodservice\Exceptions\RateLimitException If rate limit is exceeded
     *
     * @example
     * ```php
     * try {
     *     // Get security token first
     *     $tokenInfo = $client->files->getSecurityToken(12345);
     *
     *     // Download image with custom dimensions
     *     $imageData = $client->files->getImage(
     *         $tokenInfo->FileId,
     *         $tokenInfo->SecurityToken,
     *         800,  // width
     *         600   // height
     *     );
     *
     *     // Save to file
     *     file_put_contents('product.jpg', $imageData);
     *
     *     // Or output directly
     *     header('Content-Type: image/jpeg');
     *     echo $imageData;
     * } catch (PSApiException $e) {
     *     echo "Error: " . $e->getMessage();
     * }
     * ```
     */
    public function getImage(int $fileId, string $securityToken, int $width = 500, int $height = 500): ?string
    {
        try {
            if ($fileId <= 0) {
                throw new PSApiException('File ID must be greater than 0', 400);
            }

            if ($width <= 0 || $height <= 0) {
                throw new PSApiException('Width and height must be greater than 0', 400);
            }

            $response = $this->client->getHttpClient()->get("/image/{$fileId}/{$securityToken}", [
                'query' => [
                    'width' => $width,
                    'height' => $height
                ],
                'headers' => [
                    'Accept' => 'image/*'
                ]
            ]);

            $data = $response->getBody()->getContents();

            if (empty($data)) {
                return null;
            }

            return $data;

        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();

            if ($statusCode === 404) {
                return null;
            }

            $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);
            throw new PSApiException(
                $errorResponse['detail'] ?? $errorResponse['title'] ?? 'Unknown error occurred',
                $statusCode,
                $errorResponse['traceId'] ?? null
            );
        } catch (ServerException | ConnectException $e) {
            throw new PSApiException($e->getMessage(), 500);
        }
    }

    /**
     * Downloads a document file from the API.
     *
     * Returns the raw document data (bytes) along with content type information.
     * Use this for downloading PDFs, Word documents, Excel files, etc.
     *
     * **No authentication required** - Uses security token for access control
     *
     * **Rate Limiting:** 15 requests per second
     *
     * @param int $fileId The ID of the document file
     * @param string $securityToken The security token for accessing the document
     * @return array|null Array with 'data' (bytes), 'contentType', and 'fileName', or null if not found
     * @throws PSApiException If retrieval of the document fails
     * @throws \PSinfoodservice\Exceptions\RateLimitException If rate limit is exceeded
     *
     * @example
     * ```php
     * try {
     *     // Get security token first
     *     $tokenInfo = $client->files->getSecurityToken(67890);
     *
     *     // Download document
     *     $document = $client->files->getDocument(
     *         $tokenInfo->FileId,
     *         $tokenInfo->SecurityToken
     *     );
     *
     *     if ($document) {
     *         // Save to file
     *         file_put_contents($document['fileName'], $document['data']);
     *         echo "Downloaded: {$document['fileName']} ({$document['contentType']})\n";
     *
     *         // Or send as download to browser
     *         header("Content-Type: {$document['contentType']}");
     *         header("Content-Disposition: attachment; filename=\"{$document['fileName']}\"");
     *         echo $document['data'];
     *     }
     * } catch (PSApiException $e) {
     *     echo "Error: " . $e->getMessage();
     * }
     * ```
     */
    public function getDocument(int $fileId, string $securityToken): ?array
    {
        try {
            if ($fileId <= 0) {
                throw new PSApiException('File ID must be greater than 0', 400);
            }

            $response = $this->client->getHttpClient()->get("/document/{$fileId}/{$securityToken}");

            $data = $response->getBody()->getContents();

            if (empty($data)) {
                return null;
            }

            // Extract content type and filename from headers
            $contentType = $response->getHeaderLine('Content-Type') ?: 'application/octet-stream';

            // Try to extract filename from Content-Disposition header
            $fileName = null;
            $contentDisposition = $response->getHeaderLine('Content-Disposition');
            if (!empty($contentDisposition)) {
                if (preg_match('/filename[^;=\n]*=(([\'"]).*?\2|[^;\n]*)/', $contentDisposition, $matches)) {
                    $fileName = trim($matches[1], '"\'');
                }
            }

            // Fallback filename if not in header
            if (empty($fileName)) {
                $extension = $this->getExtensionFromContentType($contentType);
                $fileName = "document_{$fileId}.{$extension}";
            }

            return [
                'data' => $data,
                'contentType' => $contentType,
                'fileName' => $fileName
            ];

        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();

            if ($statusCode === 404) {
                return null;
            }

            $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);
            throw new PSApiException(
                $errorResponse['detail'] ?? $errorResponse['title'] ?? 'Unknown error occurred',
                $statusCode,
                $errorResponse['traceId'] ?? null
            );
        } catch (ServerException | ConnectException $e) {
            throw new PSApiException($e->getMessage(), 500);
        }
    }

    /**
     * Get file extension from content type
     *
     * @param string $contentType The MIME content type
     * @return string The file extension
     */
    private function getExtensionFromContentType(string $contentType): string
    {
        $map = [
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/vnd.ms-powerpoint' => 'ppt',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            'text/plain' => 'txt',
            'text/csv' => 'csv',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/bmp' => 'bmp',
            'application/zip' => 'zip',
        ];

        // Remove charset and other parameters from content type
        $contentType = strtolower(explode(';', $contentType)[0]);

        return $map[$contentType] ?? 'bin';
    }
}
