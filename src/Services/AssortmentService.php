<?php

declare(strict_types=1);
namespace PSinfoodservice\Services;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException;
use PSinfoodservice\Exceptions\PSApiException;
use PSinfoodservice\PSinfoodserviceClient;

/**
 * Service for retrieving and managing assortment lists.
 */
class AssortmentService
{
    /**
     * Initializes a new instance of the AssortmentService.
     *
     * @param PSinfoodserviceClient $client The PS in foodservice client
     */
    public function __construct(
        private PSinfoodserviceClient $client
    ) {}

    /**
     * Retrieves all assortment lists.
     *
     * **Rate Limiting:** This endpoint is subject to rate limiting. Configure automatic
     * handling with `$client->setRateLimitAutoWait(true)` to wait and retry automatically.
     *
     * @return array|null Array of assortment lists or null if none found
     * @throws PSApiException If an API error occurs
     * @throws \PSinfoodservice\Exceptions\RateLimitException If rate limit is exceeded
     */
    public function getAssortmentLists(): ?array
    {
        try {
            $response = $this->client->getHttpClient()->get($this->client->buildApiPath('Assortment/assortments'));
            $data = json_decode($response->getBody()->getContents()); 

            if (empty($data)) {
                return null;
            }

            return $data;
        } catch (ClientException $e) {
            $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);
            throw new PSApiException(
                $errorResponse['detail'] ?? $errorResponse['title'] ?? 'Unknown error occurred',
                $e->getResponse()->getStatusCode(),
                $errorResponse['traceId'] ?? null
            );
        } catch (ServerException | ConnectException $e) {
            throw new PSApiException($e->getMessage(), 500);
        }
    }

    /**
     * Retrieves a specific assortment list by ID.
     *
     * **Rate Limiting:** This endpoint is subject to rate limiting.
     *
     * @param string $id The assortment list ID
     * @param int $pageNumber Page number (default: 1)
     * @param int $pageSize Items per page (default: 250, max: 1000)
     * @return object|null The assortment list or null if not found
     * @throws PSApiException If an API error occurs
     * @throws \PSinfoodservice\Exceptions\RateLimitException If rate limit is exceeded
     */
    public function getAssortmentList(string $id, int $pageNumber = 1, int $pageSize = 250): ?object
    {
        try {
            $pageNumber = max(1, $pageNumber);
            $pageSize = max(1, min(1000, $pageSize));

            $response = $this->client->getHttpClient()->get(
                $this->client->buildApiPath("Assortment/assortments/{$id}/items"),
                [
                    'query' => [
                        'pageNumber' => $pageNumber,
                        'pageSize' => $pageSize
                    ]
                ]
            );
            $data = json_decode($response->getBody()->getContents());

            return $data;
        } catch (ClientException $e) {
            $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);
            throw new PSApiException(
                $errorResponse['detail'] ?? $errorResponse['title'] ?? 'Unknown error occurred',
                $e->getResponse()->getStatusCode(),
                $errorResponse['traceId'] ?? null
            );
        } catch (ServerException | ConnectException $e) {
            throw new PSApiException($e->getMessage(), 500);
        }
    }

    /**
     * Creates a new assortment list.
     *
     * This method sends a POST request to create a new assortment with an optional list of items.
     * Each item can be identified by GTIN (CE or HE), article number, or other identifying fields.
     *
     * **Required Role:** Read
     *
     * **Rate Limiting:** 1 request per second
     *
     * @param string $name The name of the assortment list
     * @param array|null $items Optional array of items to add to the assortment.
     *                          Each item can contain: GTINCE, GTINHE, ArticleNumber, etc.
     * @return string The GUID of the newly created assortment
     * @throws PSApiException If the creation fails or validation errors occur
     *
     * @example
     * ```php
     * // Create an empty assortment
     * $assortmentId = $client->assortment->createAssortment('My Product List');
     *
     * // Create an assortment with items
     * $items = [
     *     ['GTINCE' => '1234567890123'],
     *     ['GTINHE' => '9876543210987'],
     *     ['ArticleNumber' => 'ART-001']
     * ];
     * $assortmentId = $client->assortment->createAssortment('My Product List', $items);
     * echo "Created assortment with ID: {$assortmentId}";
     * ```
     */
    public function createAssortment(string $name, ?array $items = null): string
    {
        try {
            $data = [
                'Name' => $name
            ];

            if ($items !== null && count($items) > 0) {
                $data['Items'] = $items;
            }

            $response = $this->client->getHttpClient()->post(
                $this->client->buildApiPath('Assortment/assortments'),
                [
                    'json' => $data,
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json'
                    ]
                ]
            );

            $responseData = json_decode($response->getBody()->getContents(), true);

            // API returns the GUID as a string
            return $responseData;

        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);

            throw new PSApiException(
                $errorResponse['detail'] ?? $errorResponse['title'] ?? 'Failed to create assortment',
                $statusCode,
                $errorResponse['traceId'] ?? null
            );
        } catch (ServerException | ConnectException $e) {
            throw new PSApiException($e->getMessage(), 500);
        }
    }

    /**
     * Adds items to an existing assortment list.
     *
     * This method sends a PUT request to append items to an existing assortment.
     * Items are identified by GTIN codes, article numbers, or other identifying fields.
     *
     * **Required Role:** Read
     *
     * **Rate Limiting:** 2 requests per second
     *
     * @param string $assortmentId The GUID of the assortment list
     * @param array $items Array of items to add. Each item can contain:
     *                     - GTINCE: Consumer unit GTIN
     *                     - GTINHE: Handling/Dispatch unit GTIN
     *                     - ArticleNumber: Article number
     *                     - RelationArticleNumber: Relation-specific article number
     * @return bool True if items were added successfully
     * @throws PSApiException If the operation fails (e.g., assortment not found)
     *
     * @example
     * ```php
     * $items = [
     *     ['GTINCE' => '1234567890123'],
     *     ['GTINHE' => '9876543210987'],
     *     ['ArticleNumber' => 'ART-001', 'ArticleName' => 'Product Name']
     * ];
     *
     * try {
     *     $success = $client->assortment->addItems('550e8400-e29b-41d4-a716-446655440000', $items);
     *     if ($success) {
     *         echo "Items added successfully!";
     *     }
     * } catch (PSApiException $e) {
     *     if ($e->getStatusCode() === 409) {
     *         echo "Assortment not found";
     *     }
     * }
     * ```
     */
    public function addItems(string $assortmentId, array $items): bool
    {
        try {
            $data = [
                'Id' => $assortmentId,
                'Items' => $items
            ];

            $response = $this->client->getHttpClient()->put(
                $this->client->buildApiPath('Assortment/assortments/items'),
                [
                    'json' => $data,
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json'
                    ]
                ]
            );

            return $response->getStatusCode() === 200;

        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);

            // 409 Conflict means assortment not found
            if ($statusCode === 409) {
                throw new PSApiException(
                    "Assortment with ID {$assortmentId} not found",
                    $statusCode,
                    $errorResponse['traceId'] ?? null
                );
            }

            throw new PSApiException(
                $errorResponse['detail'] ?? $errorResponse['title'] ?? 'Failed to add items to assortment',
                $statusCode,
                $errorResponse['traceId'] ?? null
            );
        } catch (ServerException | ConnectException $e) {
            throw new PSApiException($e->getMessage(), 500);
        }
    }

    /**
     * Removes items from an existing assortment list.
     *
     * This method sends a DELETE request to remove specific items from an assortment.
     * Items are identified by GTIN codes, article numbers, or other identifying fields.
     *
     * **Required Role:** Read
     *
     * **Rate Limiting:** 1 request per second
     *
     * @param string $assortmentId The GUID of the assortment list
     * @param array $items Array of items to remove. Each item should contain identifying fields:
     *                     - GTINCE: Consumer unit GTIN
     *                     - GTINHE: Handling/Dispatch unit GTIN
     *                     - ArticleNumber: Article number
     * @return bool True if items were removed successfully
     * @throws PSApiException If the operation fails (e.g., assortment not found)
     *
     * @example
     * ```php
     * $itemsToRemove = [
     *     ['GTINCE' => '1234567890123'],
     *     ['ArticleNumber' => 'ART-001']
     * ];
     *
     * try {
     *     $success = $client->assortment->removeItems('550e8400-e29b-41d4-a716-446655440000', $itemsToRemove);
     *     if ($success) {
     *         echo "Items removed successfully!";
     *     }
     * } catch (PSApiException $e) {
     *     if ($e->getStatusCode() === 409) {
     *         echo "Assortment not found";
     *     }
     * }
     * ```
     */
    public function removeItems(string $assortmentId, array $items): bool
    {
        try {
            $data = [
                'Items' => $items
            ];

            $response = $this->client->getHttpClient()->delete(
                $this->client->buildApiPath("Assortment/assortments/{$assortmentId}/items"),
                [
                    'json' => $data,
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json'
                    ]
                ]
            );

            return $response->getStatusCode() === 200;

        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);

            // 409 Conflict means assortment not found
            if ($statusCode === 409) {
                throw new PSApiException(
                    "Assortment with ID {$assortmentId} not found",
                    $statusCode,
                    $errorResponse['traceId'] ?? null
                );
            }

            throw new PSApiException(
                $errorResponse['detail'] ?? $errorResponse['title'] ?? 'Failed to remove items from assortment',
                $statusCode,
                $errorResponse['traceId'] ?? null
            );
        } catch (ServerException | ConnectException $e) {
            throw new PSApiException($e->getMessage(), 500);
        }
    }

    /**
     * Deletes an entire assortment list.
     *
     * This method sends a DELETE request to permanently remove an assortment list
     * and all its associated items.
     *
     * **Required Role:** Read
     *
     * **Rate Limiting:** 1 request per second
     *
     * @param string $assortmentId The GUID of the assortment list to delete
     * @return bool True if the assortment was deleted successfully
     * @throws PSApiException If the deletion fails
     *
     * @example
     * ```php
     * try {
     *     $success = $client->assortment->deleteAssortment('550e8400-e29b-41d4-a716-446655440000');
     *     if ($success) {
     *         echo "Assortment deleted successfully!";
     *     }
     * } catch (PSApiException $e) {
     *     echo "Failed to delete assortment: " . $e->getMessage();
     * }
     * ```
     */
    public function deleteAssortment(string $assortmentId): bool
    {
        try {
            $response = $this->client->getHttpClient()->delete(
                $this->client->buildApiPath("Assortment/assortment/{$assortmentId}"),
                [
                    'headers' => [
                        'Accept' => 'application/json'
                    ]
                ]
            );

            return $response->getStatusCode() === 200;

        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);

            throw new PSApiException(
                $errorResponse['detail'] ?? $errorResponse['title'] ?? 'Failed to delete assortment',
                $statusCode,
                $errorResponse['traceId'] ?? null
            );
        } catch (ServerException | ConnectException $e) {
            throw new PSApiException($e->getMessage(), 500);
        }
    }
}
