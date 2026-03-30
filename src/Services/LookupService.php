<?php

declare(strict_types=1);
namespace PSinfoodservice\Services;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException;
use PSinfoodservice\Contracts\LookupServiceInterface;
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
class LookupService implements LookupServiceInterface {
    /**
     * Initializes a new instance of the LookupService.
     *
     * @param PSinfoodserviceClient $client The PS in foodservice client
     */
    public function __construct(
        private PSinfoodserviceClient $client
    ) {}

    /**
     * Looks up product information using Gtin numbers.
     *
     * @param RequestLookupGtin $request The lookup request containing GTIN data
     * @param bool $minimal Optional flag to use minimal endpoint that returns only IDs (default: false)
     *                      When true, returns minimal response with only identifiers.
     *                      When false, returns full product data including all details.
     * @return object|null The lookup response data or null if no data is available
     * @throws PSApiException If the lookup operation fails
     */
    public function Gtin(RequestLookupGtin $request, bool $minimal = false): ?object
    {
        $endpoint = $minimal ? 'Lookup/gtin/minimal' : 'Lookup/gtin';

        try {
            $response = $this->client->getHttpClient()->post(
                $this->client->buildApiPath($endpoint),
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
     * @param bool $minimal Optional flag to use minimal endpoint that returns only IDs (default: false)
     *                      When true, returns minimal response with only identifiers.
     *                      When false, returns full product data including all details.
     * @return object|null The lookup response data or null if no data is available
     * @throws PSApiException If the lookup operation fails
     */
    public function PsId(RequestLookupPSId $request, bool $minimal = false): ?object
    {
        $endpoint = $minimal ? 'Lookup/psId/minimal' : 'Lookup/psId';

        try {
            $response = $this->client->getHttpClient()->post(
                $this->client->buildApiPath($endpoint),
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
     * @param bool $minimal Optional flag to use minimal endpoint that returns only IDs (default: false)
     *                      When true, returns minimal response with only identifiers.
     *                      When false, returns full product data including all details.
     * @return object|null The lookup response data or null if no data is available
     * @throws PSApiException If the lookup operation fails
     */
    public function ArticleNumber(RequestLookupArticlenumber $request, bool $minimal = false): ?object
    {
        $endpoint = $minimal ? 'Lookup/articlenumber/minimal' : 'Lookup/articlenumber';

        try {
            $response = $this->client->getHttpClient()->post(
                $this->client->buildApiPath($endpoint),
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
     * @param bool $minimal Optional flag to use minimal endpoint that returns only IDs (default: false)
     *                      When true, returns minimal response with only identifiers.
     *                      When false, returns full product data including all details.
     * @return object|null The lookup response data or null if no data is available
     * @throws PSApiException If the lookup operation fails
     */
    public function GLN(RequestLookupGln $request, bool $minimal = false): ?object
    {
        $endpoint = $minimal ? 'Lookup/Gln/minimal' : 'Lookup/Gln';

        try {
            $response = $this->client->getHttpClient()->post(
                $this->client->buildApiPath($endpoint),
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
     * @param bool $minimal Optional flag to use minimal endpoint that returns only IDs (default: false)
     *                      When true, returns minimal response with only identifiers.
     *                      When false, returns full product data including all details.
     * @return object|null The lookup response data or null if no data is available
     * @throws PSApiException If the lookup operation fails
     */
    public function Assortment(RequestLookupAssortment $request, bool $minimal = false): ?object
    {
        $endpoint = $minimal ? 'Lookup/Assortment/minimal' : 'Lookup/Assortment';

        try {
            $response = $this->client->getHttpClient()->post(
                $this->client->buildApiPath($endpoint),
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
     * @param bool $minimal Optional flag to use minimal endpoint that returns only IDs (default: false)
     *                      When true, returns minimal response with only identifiers.
     *                      When false, returns full product data including all details.
     * @return object|null The lookup response data or null if no data is available
     * @throws PSApiException If the lookup operation fails
     */
    public function BrandId(RequestLookupBrandId $request, bool $minimal = false): ?object
    {
        $endpoint = $minimal ? 'Lookup/BrandId/minimal' : 'Lookup/BrandId';

        try {
            $response = $this->client->getHttpClient()->post(
                $this->client->buildApiPath($endpoint),
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
     * @param bool $minimal Optional flag to use minimal endpoint that returns only IDs (default: false)
     *                      When true, returns minimal response with only identifiers.
     *                      When false, returns full product data including all details.
     * @return object|null The lookup response data or null if no data is available
     * @throws PSApiException If the lookup operation fails
     */
    public function All(RequestLookup $request, bool $minimal = false): ?object
    {
        $endpoint = $minimal ? 'Lookup/All/minimal' : 'Lookup/All';

        try {
            $response = $this->client->getHttpClient()->post(
                $this->client->buildApiPath($endpoint),
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
