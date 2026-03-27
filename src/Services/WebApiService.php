<?php

declare(strict_types=1);
namespace PSinfoodservice\Services;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException;
use PSinfoodservice\Exceptions\PSApiException;
use PSinfoodservice\PSinfoodserviceClient;
use PSinfoodservice\Domain\Language;
use PSinfoodservice\Domain\Output;
use PSinfoodservice\Dtos\Incoming;
use PSinfoodservice\Dtos\Incoming\ProductSheetUpdateDto;
use PSinfoodservice\Dtos\Outgoing\ResponseDto;
use PSinfoodservice\Dtos\Outgoing\HeaderDto;
use PSinfoodservice\Dtos\Outgoing\ValidationErrorDto;

/**
 * Service for accessing web API functionality in the PS in foodservice API.
 */
class WebApiService
{
    /**
     * Initializes a new instance of the WebApiService.
     *
     * @param PSinfoodserviceClient $client The PS in foodservice client
     */
    public function __construct(
        private PSinfoodserviceClient $client
    ) {}

    /**
     * Retrieves a product sheet by logistic ID.
     *
     * **Rate Limiting:** This endpoint is subject to rate limiting. If you exceed
     * the rate limit, a RateLimitException will be thrown with retry information.
     * You can enable automatic rate limit handling by configuring the client with
     * `['rate_limit_auto_wait' => true]`.
     *
     * @param int $logisticId The ID of the logistics item
     * @param string $language The language code for the product sheet
     * @return object|null The product sheet data or null if not available
     * @throws PSApiException If retrieval of the product sheet fails
     * @throws \PSinfoodservice\Exceptions\RateLimitException If rate limit is exceeded
     *
     * @example
     * ```php
     * use PSinfoodservice\Exceptions\RateLimitException;
     *
     * try {
     *     $productSheet = $client->webApi->getProductSheet(12345, 'NL');
     * } catch (RateLimitException $e) {
     *     // Rate limit exceeded - wait and retry
     *     $waitSeconds = $e->getRetryAfter();
     *     echo "Rate limit hit. Waiting {$waitSeconds} seconds...\n";
     *     sleep($waitSeconds);
     *     $productSheet = $client->webApi->getProductSheet(12345, 'NL');
     * }
     * ```
     */
    public function getProductSheet(int $logisticId, string $language = Language::all): ?object
    {
        try {
            if($language == Language::all) {
                $response = $this->client->getHttpClient()->get(
                    $this->client->buildApiPath("ProductSheet/{$logisticId}")
                );
            }else{
                $language = Language::validate($language);
                $response = $this->client->getHttpClient()->get(
                    $this->client->buildApiPath("ProductSheet/{$language}/{$logisticId}")
                );
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
     * **Rate Limiting:** This endpoint is subject to rate limiting. If you exceed
     * the rate limit, a RateLimitException will be thrown unless automatic handling
     * is enabled via `$client->setRateLimitAutoWait(true)`.
     *
     * @return array|null Array of user's products or null if no products are available
     * @throws PSApiException If retrieval fails
     * @throws \PSinfoodservice\Exceptions\RateLimitException If rate limit is exceeded
     */
    public function getMyProducts(): ?array
    {
        try {
            $response = $this->client->getHttpClient()->get(
                $this->client->buildApiPath("MyProducts")
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

    /**
     * Updates or creates a product sheet in the PS in foodservice API.
     *
     * This method sends a PUT request to update product information including logistic,
     * product, and specification data. The API performs validation and applies business
     * logic rules before saving the data.
     *
     * **Required Role:** Publish
     *
     * **Rate Limiting:** 5 requests per second. If exceeded, a RateLimitException will be
     * thrown with retry information. Enable automatic handling with `['rate_limit_auto_wait' => true]`
     * in the client configuration to have the SDK automatically wait and retry.
     *
     * @param ProductSheetUpdateDto|array $productSheet The product sheet data to update.
     *                                                   Can be a ProductSheetUpdateDto object or an associative array.
     * @return ResponseDto The response containing success status, logistic ID, and any validation errors
     * @throws PSApiException If the update fails due to client or server errors
     * @throws \PSinfoodservice\Exceptions\RateLimitException If rate limit is exceeded
     *
     * @example
     * ```php
     * // Create a product sheet update
     * $productSheet = [
     *     'logistic' => [
     *         'gtin' => '1234567890123',
     *         'name' => [
     *             ['LanguageId' => 18, 'Value' => 'Product Name']
     *         ],
     *         'isbaseunit' => true,
     *         'isconsumerunit' => true,
     *         'logistictypeid' => 1,
     *         'package' => [
     *             'height' => ['Value' => 10, 'UnitId' => 1],
     *             'width' => ['Value' => 10, 'UnitId' => 1],
     *             'depth' => ['Value' => 10, 'UnitId' => 1]
     *         ]
     *     ],
     *     'product' => [
     *         'name' => [
     *             ['LanguageId' => 18, 'Value' => 'Product Name']
     *         ],
     *         'validfrom' => '2024-01-01T00:00:00'
     *     ]
     * ];
     *
     * try {
     *     $response = $client->webApi->updateProductSheet($productSheet);
     *
     *     if ($response->IsSucceeded) {
     *         echo "Product updated successfully! Logistic ID: " . $response->LogisticId;
     *     } else {
     *         echo "Validation errors occurred:\n";
     *         foreach ($response->Error as $error) {
     *             echo "- {$error->Position}: {$error->ErrorMessage}\n";
     *         }
     *     }
     * } catch (PSApiException $e) {
     *     echo "API Error: " . $e->getMessage();
     *     echo "Status Code: " . $e->getStatusCode();
     *     if ($e->getTraceId()) {
     *         echo "Trace ID: " . $e->getTraceId();
     *     }
     * }
     * ```
     */
    public function updateProductSheet($productSheet): ResponseDto
    {
        try {
            // Convert ProductSheetUpdateDto to array if needed
            $data = is_array($productSheet) ? $productSheet : json_decode(json_encode($productSheet), true);

            $response = $this->client->getHttpClient()->put(
                $this->client->buildApiPath("productsheet"),
                [
                    'json' => $data,
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json'
                    ]
                ]
            );

            $responseData = json_decode($response->getBody()->getContents(), true);

            // Map response to ResponseDto
            $responseDto = new ResponseDto();
            $responseDto->IsSucceeded = $responseData['isSucceeded'] ?? $responseData['IsSucceeded'] ?? false;
            $responseDto->LogisticId = $responseData['logisticId'] ?? $responseData['LogisticId'] ?? 0;
            $responseDto->ErrorMessage = $responseData['errorMessage'] ?? $responseData['ErrorMessage'] ?? null;
            $responseDto->TraceId = $responseData['traceId'] ?? $responseData['TraceId'] ?? null;

            // Map header if present
            if (isset($responseData['header']) || isset($responseData['Header'])) {
                $headerData = $responseData['header'] ?? $responseData['Header'];
                $header = new HeaderDto();
                $header->Provider = $headerData['provider'] ?? $headerData['Provider'] ?? 'PS In Foodservice';
                $header->Version = $headerData['version'] ?? $headerData['Version'] ?? '7.0.0.0';
                $header->ActionType = $headerData['actiontype'] ?? $headerData['actionType'] ?? $headerData['ActionType'] ?? 'PUT';
                $header->TraceId = $headerData['traceid'] ?? $headerData['traceiId'] ?? $headerData['TraceId'] ?? $headerData['TraceiId'] ?? null;
                $header->ExecutionTime = $headerData['executiontime'] ?? $headerData['executionTime'] ?? $headerData['ExecutionTime'] ?? '0';
                $responseDto->Header = $header;
            }

            // Map validation errors if present
            if (isset($responseData['error']) || isset($responseData['Error']) || isset($responseData['errors']) || isset($responseData['Errors'])) {
                $errorsData = $responseData['error'] ?? $responseData['Error'] ?? $responseData['errors'] ?? $responseData['Errors'];
                if (is_array($errorsData)) {
                    $errors = [];
                    foreach ($errorsData as $errorData) {
                        $error = new ValidationErrorDto();
                        $error->Position = $errorData['position'] ?? $errorData['Position'] ?? null;
                        $error->ErrorMessage = $errorData['errorMessage'] ?? $errorData['ErrorMessage'] ?? null;
                        $errors[] = $error;
                    }
                    $responseDto->Error = $errors;
                }
            }

            return $responseDto;

        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);

            // Handle 400 Bad Request with validation errors
            if ($statusCode === 400 && isset($errorResponse['error'])) {
                // Return a ResponseDto with the error instead of throwing
                $responseDto = new ResponseDto();
                $responseDto->IsSucceeded = false;
                $responseDto->ErrorMessage = is_string($errorResponse['error']) ? $errorResponse['error'] : json_encode($errorResponse['error']);
                $responseDto->TraceId = $errorResponse['traceId'] ?? null;
                return $responseDto;
            }

            throw new PSApiException(
                $errorResponse['detail'] ?? $errorResponse['title'] ?? $errorResponse['error'] ?? 'Unknown error occurred',
                $statusCode,
                $errorResponse['traceId'] ?? null
            );
        } catch (ServerException | ConnectException $e) {
            throw new PSApiException($e->getMessage(), 500);
        }
    }
}
