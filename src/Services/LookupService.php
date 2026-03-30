<?php
namespace PSinfoodservice\Services;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException;
use PSinfoodservice\Exceptions\PSApiException;
use PSinfoodservice\PSinfoodserviceClient;
use PSinfoodservice\Domain\RequestLookupGtin;
use PSinfoodservice\Domain\RequestLookupPSId;
use PSinfoodservice\Domain\RequestLookupArticlenumber;
use PSinfoodservice\Domain\RequestLookupGln;
use PSinfoodservice\Domain\RequestLookupAssortment;
use PSinfoodservice\Domain\RequestLookupBrandId;
use PSinfoodservice\Domain\RequestLookup;

/**
 * Service for handling product lookup operations in the PS in foodservice API.
 */
class LookupService {
    /**
     * The PS in foodservice client instance.
     */
    private PSinfoodserviceClient $client;

    /**
     * Initializes a new instance of the LookupService.
     *
     * @param PSinfoodserviceClient $client The PS in foodservice client
     */
    public function __construct(PSinfoodserviceClient $client)
    {
        $this->client = $client;
    }

    /**
     * Looks up product information using Gtin numbers.
     *
     * @param RequestLookupGtin $request The lookup request containing GTIN data
     * @return object|null The lookup response data or null if no data is available
     * @throws PSApiException If the lookup operation fails
     */
    public function Gtin(RequestLookupGtin $request): ?object
    {
        try {
            $response = $this->client->getHttpClient()->post(
                $this->client->buildApiPath('Lookup/gtin'),
                ['json' => $request]
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
     * Looks up product information using PS IDs.
     *
     * @param RequestLookupPSId $request The lookup request containing PS ID data
     * @return object|null The lookup response data or null if no data is available
     * @throws PSApiException If the lookup operation fails
     */
    public function PsId(RequestLookupPSId $request): ?object
    {
        try {
            $response = $this->client->getHttpClient()->post(
                $this->client->buildApiPath('Lookup/psId'),
                ['json' => $request]
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
     * Looks up product information using article numbers.
     *
     * @param RequestLookupArticlenumber $request The lookup request containing article number data
     * @return object|null The lookup response data or null if no data is available
     * @throws PSApiException If the lookup operation fails
     */
    public function ArticleNumber(RequestLookupArticlenumber $request): ?object
    {
        try {
            $response = $this->client->getHttpClient()->post(
                $this->client->buildApiPath('Lookup/articlenumber'),
                ['json' => $request]
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
     * Looks up product information using GLN (Global Location Number).
     *
     * @param RequestLookupGln $request The lookup request containing GLN data
     * @return object|null The lookup response data or null if no data is available
     * @throws PSApiException If the lookup operation fails
     */
    public function GLN(RequestLookupGln $request): ?object
    {
        try {
            $response = $this->client->getHttpClient()->post(
                $this->client->buildApiPath('Lookup/Gln'),
                ['json' => $request]
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
            /**
             * Service for handling product lookup operations in the PS in foodservice API.
             */
        }
    }

    /**
     * Looks up product assortment information.
     *
     * @param RequestLookupAssortment $request The lookup request containing assortment data
     * @return object|null The lookup response data or null if no data is available
     * @throws PSApiException If the lookup operation fails
     */ 
    public function Assortment(RequestLookupAssortment $request): ?object
    {
        try {
            $response = $this->client->getHttpClient()->post(
                $this->client->buildApiPath('Lookup/Assortment'),
                ['json' => $request]
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
     * Looks up product by brandid. Not available for all users.
     *
     * @param RequestLookupBrandId $request The lookup request containing brandid 
     * @return object|null The lookup response data or null if no data is available
     * @throws PSApiException If the lookup operation fails
     */ 
    public function BrandId(RequestLookupBrandId $request): ?object
    {
        try {
            $response = $this->client->getHttpClient()->post(
                $this->client->buildApiPath('Lookup/BrandId'),
                ['json' => $request]
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
     * Looks up all products by changedate. Not available for all users.
     *
     * @param RequestLookup $request The lookup request containing change date
     * @return object|null The lookup response data or null if no data is available
     * @throws PSApiException If the lookup operation fails
     */ 
    public function All(RequestLookup $request): ?object
    {
        try {
            $response = $this->client->getHttpClient()->post(
                $this->client->buildApiPath('Lookup/All'),
                ['json' => $request]
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
}