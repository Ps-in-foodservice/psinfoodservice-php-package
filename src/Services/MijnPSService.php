<?php

declare(strict_types=1);
namespace PSinfoodservice\Services;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException;
use PSinfoodservice\Exceptions\PSApiException;
use PSinfoodservice\PSinfoodserviceClient;

/**
 * Service for MijnPS-specific operations.
 *
 * Provides functionality for uploading assortment files to the API.
 */
class MijnPSService
{
    /**
     * Initializes a new instance of the MijnPSService.
     *
     * @param PSinfoodserviceClient $client The PS in foodservice client
     */
    public function __construct(
        private PSinfoodserviceClient $client
    ) {}

    /**
     * Upload an assortment file to the API.
     *
     * Uploads a file (e.g., Excel or CSV) containing assortment data
     * to be processed by the API.
     *
     * **Note:** This endpoint requires appropriate permissions.
     *
     * @param string $assortmentId The GUID of the assortment to update
     * @param string $filePath The path to the file to upload
     * @param string|null $fileName Optional custom filename (defaults to basename of filePath)
     * @return bool True if upload was successful
     * @throws PSApiException If the upload fails
     * @throws \InvalidArgumentException If the file does not exist
     *
     * @example
     * ```php
     * try {
     *     $success = $client->mijnPS->uploadAssortment(
     *         '00000000-0000-0000-0000-000000000000',
     *         '/path/to/assortment.xlsx'
     *     );
     *
     *     if ($success) {
     *         echo "Assortment uploaded successfully\n";
     *     }
     * } catch (PSApiException $e) {
     *     echo "Upload failed: " . $e->getMessage();
     * }
     * ```
     */
    public function uploadAssortment(string $assortmentId, string $filePath, ?string $fileName = null): bool
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("File not found: {$filePath}");
        }

        if (!$this->isValidGuid($assortmentId)) {
            throw new \InvalidArgumentException("Invalid assortment ID format. Expected GUID.");
        }

        $fileName = $fileName ?? basename($filePath);

        try {
            $response = $this->client->getHttpClient()->post(
                '/v7/MijnPS/uploadassortiment',
                [
                    'multipart' => [
                        [
                            'name' => 'assortimentId',
                            'contents' => $assortmentId
                        ],
                        [
                            'name' => 'file',
                            'contents' => fopen($filePath, 'r'),
                            'filename' => $fileName
                        ]
                    ]
                ]
            );

            return $response->getStatusCode() === 200;

        } catch (ClientException $e) {
            $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);
            throw new PSApiException(
                $errorResponse['detail'] ?? $errorResponse['title'] ?? 'Upload failed',
                $e->getResponse()->getStatusCode(),
                $errorResponse['traceId'] ?? null
            );
        } catch (ServerException | ConnectException $e) {
            throw new PSApiException($e->getMessage(), 500);
        }
    }

    /**
     * Upload assortment data from a string content.
     *
     * Useful when the file content is already in memory.
     *
     * @param string $assortmentId The GUID of the assortment to update
     * @param string $content The file content to upload
     * @param string $fileName The filename to use for the upload
     * @return bool True if upload was successful
     * @throws PSApiException If the upload fails
     *
     * @example
     * ```php
     * $csvContent = "GTIN,ArticleNumber\n1234567890123,ABC-001";
     * $success = $client->mijnPS->uploadAssortmentContent(
     *     '00000000-0000-0000-0000-000000000000',
     *     $csvContent,
     *     'assortment.csv'
     * );
     * ```
     */
    public function uploadAssortmentContent(string $assortmentId, string $content, string $fileName): bool
    {
        if (!$this->isValidGuid($assortmentId)) {
            throw new \InvalidArgumentException("Invalid assortment ID format. Expected GUID.");
        }

        if (empty($content)) {
            throw new \InvalidArgumentException("Content cannot be empty");
        }

        try {
            $response = $this->client->getHttpClient()->post(
                '/v7/MijnPS/uploadassortiment',
                [
                    'multipart' => [
                        [
                            'name' => 'assortimentId',
                            'contents' => $assortmentId
                        ],
                        [
                            'name' => 'file',
                            'contents' => $content,
                            'filename' => $fileName
                        ]
                    ]
                ]
            );

            return $response->getStatusCode() === 200;

        } catch (ClientException $e) {
            $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);
            throw new PSApiException(
                $errorResponse['detail'] ?? $errorResponse['title'] ?? 'Upload failed',
                $e->getResponse()->getStatusCode(),
                $errorResponse['traceId'] ?? null
            );
        } catch (ServerException | ConnectException $e) {
            throw new PSApiException($e->getMessage(), 500);
        }
    }

    /**
     * Validate if a string is a valid GUID format.
     *
     * @param string $guid The string to validate
     * @return bool True if valid GUID format
     */
    private function isValidGuid(string $guid): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $guid) === 1;
    }
}
