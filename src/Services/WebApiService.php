<?php
namespace PSinfoodservice\Services;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException; 
use PSinfoodservice\Exceptions\PSApiException;
use PSinfoodservice\PSinfoodserviceClient;
use PSinfoodservice\Domain\Language;

/**
 * Service for accessing web API functionality in the PS in foodservice API.
 */
class WebApiService
{
    /**
     * The PS in foodservice client instance.
     */
    private PSinfoodserviceClient $client;

    /**
     * Initializes a new instance of the WebApiService.
     *
     * @param PSinfoodserviceClient $client The PS in foodservice client
     */
    public function __construct(PSinfoodserviceClient $client)
    {
        $this->client = $client;
    }

    /**
     * Retrieves a product sheet by logistic ID.
     *
     * @param int $logisticId The ID of the logistics item
     * @param string $output The output format or section ('all' for complete data)
     * @param string $language The language code for the product sheet
     * @return object|null The product sheet data or null if not available
     * @throws PSApiException If retrieval of the product sheet fails
     */
    public function getProductSheet(int $logisticId, string $output = Output::all, string $language = Language::all): ?object
    {
        try {
            if($language == Language::all) {
                $response = $this->client->getHttpClient()->get("/v7/json/ProductSheet/{$output}/{$logisticId}"); 
            }else{ 
                $language = Language::validate($language);
                $response = $this->client->getHttpClient()->get("/v7/json/ProductSheet/{$language}/{$output}/{$logisticId}");
            }
            $data = json_decode($response->getBody()->getContents());

            if (empty($data)) {
                return null;
            }

            return $data;
        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            if ($statusCode === 403) {
                return null;
            }

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
     * Retrieves products associated with the current user.
     *
     * @return array|null Array of user's products or null if no products are available
     */
    public function getMyProducts(): ?array
    {
        try {
            $response = $this->client->getHttpClient()->get(
                "/v7/json/MyProducts"
            );
            $data = json_decode($response->getBody()->getContents());
            if (empty($data)) {
                return null;
            }

            return $data->Items; 
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