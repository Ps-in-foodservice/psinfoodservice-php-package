<?php

declare(strict_types=1);
namespace PSinfoodservice\Services;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException;
use PSinfoodservice\Exceptions\PSApiException;
use PSinfoodservice\PSinfoodserviceClient;
use PSinfoodservice\Dtos\Outgoing\AssetDto;

/**
 * Service for retrieving asset information from the PS in foodservice API.
 *
 * Assets are digital files (images, documents) associated with products.
 * This service provides access to asset metadata and retrieval.
 */
class AssetService
{
    /**
     * Initializes a new instance of the AssetService.
     *
     * @param PSinfoodserviceClient $client The PS in foodservice client
     */
    public function __construct(
        private PSinfoodserviceClient $client
    ) {}

    /**
     * Retrieves a specific asset by its ID.
     *
     * Returns detailed metadata about an asset including type, dimensions,
     * and associated file information.
     *
     * **Required Role:** Read
     *
     * **Rate Limiting:** No limiting
     *
     * @param int $assetId The ID of the asset to retrieve
     * @return AssetDto|null The asset information or null if not found
     * @throws PSApiException If retrieval fails
     *
     * @example
     * ```php
     * try {
     *     $asset = $client->assets->getAsset(12345);
     *
     *     if ($asset !== null) {
     *         echo "Asset ID: {$asset->Id}\n";
     *         echo "Type: {$asset->AssetType}\n";
     *         echo "File ID: {$asset->FileId}\n";
     *     }
     * } catch (PSApiException $e) {
     *     echo "Error: " . $e->getMessage();
     * }
     * ```
     */
    public function getAsset(int $assetId): ?AssetDto
    {
        try {
            if ($assetId <= 0) {
                throw new PSApiException('Asset ID must be greater than 0', 400);
            }

            $response = $this->client->getHttpClient()->get(
                $this->client->buildApiPath("Asset/asset/{$assetId}")
            );

            $data = json_decode($response->getBody()->getContents(), true);

            if (empty($data)) {
                return null;
            }

            return AssetDto::fromData($data);

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
     * Retrieves all assets associated with a logistic item.
     *
     * Returns an array of assets (images, documents) linked to the specified
     * logistic ID. Uses the default language.
     *
     * **Required Role:** Read
     *
     * **Rate Limiting:** No limiting
     *
     * @param int $logisticId The ID of the logistic item
     * @return array|null Array of AssetDto objects or null if none found
     * @throws PSApiException If retrieval fails
     *
     * @example
     * ```php
     * try {
     *     $assets = $client->assets->getAssetsFromLogistic(12345);
     *
     *     if ($assets !== null) {
     *         echo "Found " . count($assets) . " assets\n";
     *         foreach ($assets as $asset) {
     *             echo "- {$asset->AssetType}: File ID {$asset->FileId}\n";
     *         }
     *     }
     * } catch (PSApiException $e) {
     *     echo "Error: " . $e->getMessage();
     * }
     * ```
     */
    public function getAssetsFromLogistic(int $logisticId): ?array
    {
        try {
            if ($logisticId <= 0) {
                throw new PSApiException('Logistic ID must be greater than 0', 400);
            }

            $response = $this->client->getHttpClient()->get(
                $this->client->buildApiPath("Asset/assetsfromlogistic/{$logisticId}")
            );

            $data = json_decode($response->getBody()->getContents(), true);

            if (empty($data)) {
                return null;
            }

            // Map each item to AssetDto
            if (is_array($data)) {
                return array_map(fn($item) => AssetDto::fromData($item), $data);
            }

            return null;

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
     * Retrieves all assets associated with a logistic item in a specific language.
     *
     * Returns an array of assets (images, documents) linked to the specified
     * logistic ID with labels in the requested language.
     *
     * **Required Role:** Read
     *
     * **Rate Limiting:** No limiting
     *
     * @param string $language The language code (e.g., 'NL', 'EN', 'DE')
     * @param int $logisticId The ID of the logistic item
     * @return array|null Array of AssetDto objects or null if none found
     * @throws PSApiException If retrieval fails
     *
     * @example
     * ```php
     * use PSinfoodservice\Domain\Language;
     *
     * try {
     *     $assets = $client->assets->getAssetsFromLogisticByLanguage(Language::nl, 12345);
     *
     *     if ($assets !== null) {
     *         foreach ($assets as $asset) {
     *             echo "Asset: {$asset->Label}\n";
     *         }
     *     }
     * } catch (PSApiException $e) {
     *     echo "Error: " . $e->getMessage();
     * }
     * ```
     */
    public function getAssetsFromLogisticByLanguage(string $language, int $logisticId): ?array
    {
        try {
            if ($logisticId <= 0) {
                throw new PSApiException('Logistic ID must be greater than 0', 400);
            }

            if (empty($language)) {
                throw new PSApiException('Language must not be empty', 400);
            }

            $response = $this->client->getHttpClient()->get(
                $this->client->buildApiPath("Asset/assetsfromlogistic/{$language}/{$logisticId}")
            );

            $data = json_decode($response->getBody()->getContents(), true);

            if (empty($data)) {
                return null;
            }

            // Map each item to AssetDto
            if (is_array($data)) {
                return array_map(fn($item) => AssetDto::fromData($item), $data);
            }

            return null;

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
}
