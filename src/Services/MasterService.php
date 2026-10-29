<?php

declare(strict_types=1);
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
     * Initializes a new instance of the MasterService.
     *
     * @param PSinfoodserviceClient $client The PS in foodservice client
     */
    public function __construct(
        private PSinfoodserviceClient $client
    ) {}

    /**
     * Retrieves all available master data from the API.
     *
     * @return object|null Collection of all masters or null if no masters are available
     * @throws PSApiException If retrieval of the master data fails
     */
    public function getAllMasters(): ?object
    {
        try {
            $response = $this->client->getHttpClient()->get($this->client->buildApiPath('Master/All'));
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
    public function getLogisticMasters(): ?object
    {
        try {
            $response = $this->client->getHttpClient()->get($this->client->buildApiPath('Master/Logistic'));
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
    public function getProductMasters(): ?object
    {
        try {
            $response = $this->client->getHttpClient()->get($this->client->buildApiPath('Master/Product'));
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
    public function getStorageMasters(): ?object
    {
        try {
            $response = $this->client->getHttpClient()->get($this->client->buildApiPath('Master/Storage'));
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
    public function getSpecificationMasters(): ?object
    {
        try {
            $response = $this->client->getHttpClient()->get($this->client->buildApiPath('Master/Specification'));
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
    public function getProfileMasters(): ?object
    {
        try {
            $response = $this->client->getHttpClient()->get($this->client->buildApiPath('Master/Profile'));
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
     * Retrieves assortment master data (assortment types and match types) from the API.
     *
     * @return object|null Collection of assortment masters or null if no masters are available
     * @throws PSApiException If retrieval of the master data fails
     */
    public function getAssortmentMasters(): ?object
    {
        $data = $this->request('Master/Assortment');

        if (empty($data) || empty($data->masters)) {
            return null;
        }

        return $data->masters;
    }

    /**
     * Retrieves a single master list, e.g. getMaster(MasterName::Allergens).
     *
     * @param string $name Master name, see {@see \PSinfoodservice\Domain\MasterName}
     * @return array|null The master items or null if none are available
     * @throws PSApiException If retrieval of the master data fails
     */
    public function getMaster(string $name): ?array
    {
        $data = $this->request('Master/' . rawurlencode($name));

        // Single-master responses wrap the list in one property whose name differs per
        // endpoint (e.g. "allergen", "countries"); Certificates returns a bare array.
        if (is_array($data)) {
            return $data;
        }
        if (is_object($data)) {
            foreach (get_object_vars($data) as $value) {
                if (is_array($value)) {
                    return $value;
                }
            }
        }

        return null;
    }

    /**
     * Performs a GET request and returns the decoded JSON body.
     *
     * @param string $path API path relative to the version/format prefix
     * @return mixed Decoded JSON body
     * @throws PSApiException If the request fails
     */
    private function request(string $path): mixed
    {
        try {
            $response = $this->client->getHttpClient()->get($this->client->buildApiPath($path));
            return json_decode($response->getBody()->getContents());
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
