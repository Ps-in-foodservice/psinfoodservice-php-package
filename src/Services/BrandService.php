<?php
namespace PSinfoodservice\Services;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException;
use PSinfoodservice\Exceptions\PSApiException;
use PSinfoodservice\PSinfoodserviceClient;

/**
 * Service for managing brand-related functionality in the PS in foodservice API.
 */
class BrandService
{
    /**
     * The PS in foodservice client instance.
     */
    private PSinfoodserviceClient $client;
    
    /**
     * Initializes a new instance of the BrandService.
     *
     * @param PSinfoodserviceClient $client The PS in foodservice client
     */
    public function __construct(PSinfoodserviceClient $client)
    {
        $this->client = $client;
    }

    /**
     * Retrieves all available brands from the API.
     *
     * @return array|null An array of brands or null if no brands are available
     * @throws PSApiException If retrieval of brands fails
     */
    public function All(): ?array
    {
        try {
            $response = $this->client->getHttpClient()->get($this->client->buildApiPath('Brand/All'));
            $data = json_decode($response->getBody()->getContents());

            if (empty($data) || empty($data->brands)) {
                return null;
            }

            return $data->brands;
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
     * Retrieves all brands associated with the current user.
     *
     * @return array|null An array of user's brands or null if no brands are available
     * @throws PSApiException If retrieval of brands fails
     */
    public function MyBrands(): ?array
    {
        try {
            $response = $this->client->getHttpClient()->get($this->client->buildApiPath('Brand/MyBrands'));
            $data = json_decode($response->getBody()->getContents());
            
            if (empty($data) || empty($data->brands)) {
                return null;
            }

            return $data->brands;
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