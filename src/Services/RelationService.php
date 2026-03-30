<?php

declare(strict_types=1);
namespace PSinfoodservice\Services;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException;
use PSinfoodservice\Exceptions\PSApiException;
use PSinfoodservice\PSinfoodserviceClient;

/**
 * Service for retrieving relation information from the PS in foodservice API.
 *
 * This service provides access to producer and brand owner information.
 */
class RelationService
{
    /**
     * Initializes a new instance of the RelationService.
     *
     * @param PSinfoodserviceClient $client The PS in foodservice client
     */
    public function __construct(
        private PSinfoodserviceClient $client
    ) {}

    /**
     * Retrieves all producers from the API.
     *
     * Returns a list of producer IDs that can be used for filtering or reference.
     *
     * **Required Role:** Read
     *
     * **Rate Limiting:** 2 requests per second
     *
     * @return array|null Array of producer information or null if none available
     * @throws PSApiException If retrieval fails
     * @throws \PSinfoodservice\Exceptions\RateLimitException If rate limit is exceeded
     *
     * @example
     * ```php
     * try {
     *     $producers = $client->relations->getProducers();
     *
     *     if ($producers !== null) {
     *         foreach ($producers as $producer) {
     *             echo "Producer ID: {$producer->id}\n";
     *         }
     *     }
     * } catch (PSApiException $e) {
     *     echo "Error: " . $e->getMessage();
     * }
     * ```
     */
    public function getProducers(): ?array
    {
        try {
            $response = $this->client->getHttpClient()->get(
                $this->client->buildApiPath('Relation/producers')
            );
            $data = json_decode($response->getBody()->getContents());

            if (empty($data)) {
                return null;
            }

            // Return the producers array or the full response if structure differs
            return $data->producers ?? $data->Producers ?? (is_array($data) ? $data : [$data]);
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
     * Retrieves all brand owners from the API.
     *
     * Returns a list of brand owner IDs that can be used for filtering or reference.
     *
     * **Required Role:** Read
     *
     * **Rate Limiting:** 2 requests per second
     *
     * @return array|null Array of brand owner information or null if none available
     * @throws PSApiException If retrieval fails
     * @throws \PSinfoodservice\Exceptions\RateLimitException If rate limit is exceeded
     *
     * @example
     * ```php
     * try {
     *     $brandOwners = $client->relations->getBrandOwners();
     *
     *     if ($brandOwners !== null) {
     *         foreach ($brandOwners as $owner) {
     *             echo "Brand Owner ID: {$owner->id}\n";
     *         }
     *     }
     * } catch (PSApiException $e) {
     *     echo "Error: " . $e->getMessage();
     * }
     * ```
     */
    public function getBrandOwners(): ?array
    {
        try {
            $response = $this->client->getHttpClient()->get(
                $this->client->buildApiPath('Relation/brandowners')
            );
            $data = json_decode($response->getBody()->getContents());

            if (empty($data)) {
                return null;
            }

            // Return the brandOwners array or the full response if structure differs
            return $data->brandOwners ?? $data->BrandOwners ?? (is_array($data) ? $data : [$data]);
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
}
