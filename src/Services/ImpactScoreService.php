<?php
namespace PSinfoodservice\Services;
 
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException; 
use PSinfoodservice\Exceptions\PSApiException;
use PSinfoodservice\PSinfoodserviceClient;

/**
 * Service for managing impact score operations in the PS in foodservice API.
 */
class ImpactScoreService
{
    /**
     * The PS in foodservice client instance.
     */
    private PSinfoodserviceClient $client;

    /**
     * Initializes a new instance of the ImpactScoreService.
     *
     * @param PSinfoodserviceClient $client The PS in foodservice client
     */
    public function __construct(PSinfoodserviceClient $client)
    {
        $this->client = $client;
    }

    /**
     * Retrieves all available impact scores from the API.
     *
     * @return array|null An array of impact scores or null if no scores are available
     * @throws PSApiException If retrieval of the impact scores fails
     */
    public function AllScores(): ?array
    {
        try {
            $response = $this->client->getHttpClient()->get($this->client->buildApiPath('ImpactScore/AllScores'));
            $data = json_decode($response->getBody()->getContents());

            if (empty($data) || empty($data->impactScore)) {
                return null;
            }

            return $data->impactScore;
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
     * @return object|null The impact score data or null if no data is available
     * @throws PSApiException If retrieval of the impact score fails
     */
    public function GetScore(int $logisticId) :?object
    {
        try {
            $response = $this->client->getHttpClient()->get($this->client->buildApiPath("ImpactScore/GetScore/{$logisticId}"));
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
}