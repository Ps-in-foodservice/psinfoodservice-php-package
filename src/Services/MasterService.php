<?php
namespace PSinfoodservice\Services;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException;
use PSinfoodservice\Exceptions\PSApiException;
use PSinfoodservice\PSinfoodserviceClient;

/**
 * Service for retrieving master data from the PS in foodservice API.
 */
class MasterService
{
    /**
     * The PS in foodservice client instance.
     */
    private PSinfoodserviceClient $client;

    /**
     * Initializes a new instance of the MasterService.
     *
     * @param PSinfoodserviceClient $client The PS in foodservice client
     */
    public function __construct(PSinfoodserviceClient $client)
    {
        $this->client = $client;
    }

    /**
     * Retrieves all available master data from the API.
     *
     * @return object|null Collection of all masters or null if no masters are available
     * @throws PSApiException If retrieval of the master data fails
     */
    public function GetAllMasters(): ?object
    {
        try {
            $response = $this->client->getHttpClient()->get("/v7/json/Master/All");
            $data = json_decode($response->getBody()->getContents());

            if (empty($data) || empty($data->masters)) {
                return null;
            }

            return $data->masters;
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
     * Retrieves logistic master data from the API.
     *
     * @return object|null Collection of logistic masters or null if no masters are available
     * @throws PSApiException If retrieval of the master data fails
     */
    public function GetLogisticMasters(): ?object
    {
        try {
            $response = $this->client->getHttpClient()->get("/v7/json/Master/Logistic");
            $data = json_decode($response->getBody()->getContents());

            if (empty($data) || empty($data->masters)) {
                return null;
            }

            return $data->masters;
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
     * Retrieves product master data from the API.
     *
     * @return object|null Collection of product masters or null if no masters are available
     * @throws PSApiException If retrieval of the master data fails
     */
    public function GetProductMasters(): ?object
    {
        try {
            $response = $this->client->getHttpClient()->get("/v7/json/Master/Product");
            $data = json_decode($response->getBody()->getContents());

            if (empty($data) || empty($data->masters)) {
                return null;
            }

            return $data->masters;
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
     * Retrieves storage master data from the API.
     *
     * @return object|null Collection of storage masters or null if no masters are available
     * @throws PSApiException If retrieval of the master data fails
     */
    public function GetStorageMasters(): ?object
    {
        try {
            $response = $this->client->getHttpClient()->get("/v7/json/Master/Storage");
            $data = json_decode($response->getBody()->getContents());

            if (empty($data) || empty($data->masters)) {
                return null;
            }

            return $data->masters;
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
     * Retrieves specification master data from the API.
     *
     * @return object|null Collection of specification masters or null if no masters are available
     * @throws PSApiException If retrieval of the master data fails
     */
    public function GetSpecificationMasters(): ?object
    {
        try {
            $response = $this->client->getHttpClient()->get("/v7/json/Master/Specification");
            $data = json_decode($response->getBody()->getContents());

            if (empty($data) || empty($data->masters)) {
                return null;
            }

            return $data->masters;
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
     * Retrieves profile master data from the API.
     *
     * @return object|null Collection of profile masters or null if no masters are available
     * @throws PSApiException If retrieval of the master data fails
     */
    public function GetProfileMasters(): ?object
    {
        try {
            $response = $this->client->getHttpClient()->get("/v7/json/Master/Profile");
            $data = json_decode($response->getBody()->getContents());

            if (empty($data) || empty($data->masters)) {
                return null;
            }

            return $data->masters;
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