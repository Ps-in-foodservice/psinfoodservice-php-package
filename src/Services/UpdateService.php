<?php
namespace PSinfoodservice\Services;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException;
use PSinfoodservice\Exceptions\PSApiException;
use PSinfoodservice\PSinfoodserviceClient;
use PSinfoodservice\Domain\RequestUpdateEAN;
use PSinfoodservice\Domain\RequestUpdatePSId;
use PSinfoodservice\Domain\RequestUpdateArticle;
use PSinfoodservice\Domain\RequestUpdateGln;
use PSinfoodservice\Domain\RequestUpdateAssortment;

/**
 * Service for handling product update operations in the PS in foodservice API.
 */
class UpdateService {
    /**
     * The PS in foodservice client instance.
     */
    private PSinfoodserviceClient $client;

    /**
     * Initializes a new instance of the UpdateService.
     *
     * @param PSinfoodserviceClient $client The PS in foodservice client
     */
    public function __construct(PSinfoodserviceClient $client)
    {
        $this->client = $client;
    }

    /**
     * Updates product information using EAN numbers.
     *
     * @param RequestUpdateEAN $request The update request containing EAN data
     * @return object|null The update response data or null if no data is available
     * @throws PSApiException If the update operation fails
     */
    public function Ean(RequestUpdateEAN $request): ?object
    {
        try {
            $response = $this->client->getHttpClient()->post(
                "/v7/json/Updates/Ean",
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
     * Updates product information using PS IDs.
     *
     * @param RequestUpdatePSId $request The update request containing PS ID data
     * @return object|null The update response data or null if no data is available
     * @throws PSApiException If the update operation fails
     */
    public function PsId(RequestUpdatePSId $request): ?object
    {
        try {
            $response = $this->client->getHttpClient()->post(
                "/v7/json/Update/PsId",
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
     * Updates product information using article numbers.
     *
     * @param RequestUpdateArticle $request The update request containing article number data
     * @return object|null The update response data or null if no data is available
     * @throws PSApiException If the update operation fails
     */
    public function ArticleNumber(RequestUpdateArticle $request): ?object
    {
        try {
            $response = $this->client->getHttpClient()->post(
                "/v7/json/Update/ArticleNumber",
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
     * Updates product information using GLN (Global Location Number).
     *
     * @param RequestUpdateGln $request The update request containing GLN data
     * @return object|null The update response data or null if no data is available
     * @throws PSApiException If the update operation fails
     */
    public function GLN(RequestUpdateGln $request): ?object
    {
        try {
            $response = $this->client->getHttpClient()->post(
                "/v7/json/Update/GLN",
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
             * Service for handling product update operations in the PS in foodservice API.
             */
        }
    }

    /**
     * Updates product assortment information.
     *
     * @param RequestUpdateAssortment $request The update request containing assortment data
     * @return object|null The update response data or null if no data is available
     * @throws PSApiException If the update operation fails
     */
    public function Assortment(RequestUpdateAssortment $request): ?object
    {
        try {
            $response = $this->client->getHttpClient()->post(
                "/v7/json/Update/Assortment",
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