<?php

declare(strict_types=1);
namespace PSinfoodservice\Services;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException;
use PSinfoodservice\Dtos\Outgoing\ProductImpactScoreItemDto;
use PSinfoodservice\Dtos\Outgoing\ProductImpactScoreListDto;
use PSinfoodservice\Exceptions\PSApiException;
use PSinfoodservice\PSinfoodserviceClient;

/**
 * Service for managing impact score operations in the PS in foodservice API.
 */
class ImpactScoreService
{
    /**
     * Initializes a new instance of the ImpactScoreService.
     *
     * @param PSinfoodserviceClient $client The PS in foodservice client
     */
    public function __construct(
        private PSinfoodserviceClient $client
    ) {}

    /**
     * Retrieves all available impact scores from the API.
     *
     * @return ProductImpactScoreListDto|null Impact score list or null if no scores are available
     * @throws PSApiException If retrieval of the impact scores fails
     */
    public function getAllScores(): ?ProductImpactScoreListDto
    {
        try {
            $response = $this->client->getHttpClient()->get($this->client->buildApiPath('ImpactScore/all'));
            $data = json_decode($response->getBody()->getContents());

            if (empty($data)) {
                return null;
            }

            return ProductImpactScoreListDto::fromData($data);
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
     * Retrieves a specific impact score by logistic ID.
     *
     * @param int $logisticId The ID of the logistics item
     * @return ProductImpactScoreItemDto|null The impact score data or null if no data is available
     * @throws PSApiException If retrieval of the impact score fails
     */
    public function getScore(int $logisticId): ?ProductImpactScoreItemDto
    {
        try {
            $response = $this->client->getHttpClient()->get($this->client->buildApiPath("ImpactScore/score/{$logisticId}"));
            $data = json_decode($response->getBody()->getContents());

            if (empty($data)) {
                return null;
            }

            return ProductImpactScoreItemDto::fromData($data);
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
