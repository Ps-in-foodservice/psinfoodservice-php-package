<?php
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
     * The PS in foodservice client instance.
     */
    private PSinfoodserviceClient $client;

    /**
     * Initializes a new instance of the AssortmentService.
     *
     * @param PSinfoodserviceClient $client The PS in foodservice client
     */
    public function __construct(PSinfoodserviceClient $client)
    {
        $this->client = $client;
    }

    /**
     * Retrieves all assortment lists.
     *
     * @return array|null Array of assortment lists or null if none found
     * @throws PSApiException If an API error occurs
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
     * @param string $id The assortment list ID
     * @return object|null The assortment list or null if not found
     * @throws PSApiException If an API error occurs
     */
    public function getAssortmentList(string $id): ?object
    {
        try {
            $response = $this->client->getHttpClient()->get($this->client->buildApiPath("Assortment/assortments/{$id}/items"));
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