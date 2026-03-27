<?php

declare(strict_types=1);

namespace PSinfoodservice\Services;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException;
use PSinfoodservice\Exceptions\PSApiException;
use PSinfoodservice\PSinfoodserviceClient;
use PSinfoodservice\Dtos\Incoming\ProductSheetUpdateDto;
use PSinfoodservice\Dtos\Outgoing\ValidationResultDto;
use PSinfoodservice\Dtos\Outgoing\LogicResultDto;
use PSinfoodservice\Dtos\Outgoing\HeaderDto;
use PSinfoodservice\Dtos\Outgoing\ValidationErrorDto;

/**
 * Service for validation testing in the PS in foodservice API.
 *
 * Provides endpoints to test product data against validation rules and business logic
 * before actual publication.
 */
class ValidationService
{
    /**
     * Initializes a new instance of the ValidationService.
     *
     * @param PSinfoodserviceClient $client The PS in foodservice client
     */
    public function __construct(
        private PSinfoodserviceClient $client
    ) {}

    /**
     * Tests product sheet data against validation rules only.
     *
     * This method sends product sheet data to the validations/test endpoint to check
     * if the data passes all validation rules without applying business logic.
     *
     * **Rate Limiting:** This endpoint is subject to rate limiting. If you exceed
     * the rate limit, a RateLimitException will be thrown unless automatic handling
     * is enabled via `$client->setRateLimitAutoWait(true)`.
     *
     * @param ProductSheetUpdateDto|array $productSheet The product sheet data to validate.
     *                                                   Can be a ProductSheetUpdateDto object or an associative array.
     * @return ValidationResultDto The validation result containing success status and any validation errors
     * @throws PSApiException If the validation request fails due to client or server errors
     * @throws \PSinfoodservice\Exceptions\RateLimitException If rate limit is exceeded
     *
     * @example
     * ```php
     * $productSheet = [
     *     'logistic' => [
     *         'gtin' => '1234567890123',
     *         'name' => [
     *             ['LanguageId' => 18, 'Value' => 'Product Name']
     *         ]
     *     ]
     * ];
     *
     * try {
     *     $result = $client->validation->testValidations($productSheet);
     *
     *     if ($result->IsValid) {
     *         echo "Validation passed!";
     *     } else {
     *         echo "Validation errors:\n";
     *         foreach ($result->Errors as $error) {
     *             echo "- {$error->Position}: {$error->ErrorMessage}\n";
     *         }
     *     }
     * } catch (PSApiException $e) {
     *     echo "API Error: " . $e->getMessage();
     * }
     * ```
     */
    public function testValidations($productSheet): ValidationResultDto
    {
        try {
            // Convert ProductSheetUpdateDto to array if needed
            $data = is_array($productSheet) ? $productSheet : json_decode(json_encode($productSheet), true);

            $response = $this->client->getHttpClient()->put(
                $this->client->buildApiPath("validations/test"),
                [
                    'json' => $data,
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json'
                    ]
                ]
            );

            $responseData = json_decode($response->getBody()->getContents(), true);

            return $this->mapToValidationResult($responseData);

        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);

            // Handle 400 Bad Request with validation errors
            if ($statusCode === 400 && isset($errorResponse['errors'])) {
                return $this->mapToValidationResult([
                    'isValid' => false,
                    'errors' => $errorResponse['errors'],
                    'traceId' => $errorResponse['traceId'] ?? null
                ]);
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

    /**
     * Tests product sheet data against validation rules and business logic.
     *
     * This method sends product sheet data to the validations/logic endpoint to check
     * if the data passes all validation rules and to see what business logic rules
     * would be executed if the product were published.
     *
     * If validation passes, the result will include the logic rules that would be applied.
     * If validation fails, the result will include validation errors.
     *
     * **Rate Limiting:** This endpoint is subject to rate limiting. If you exceed
     * the rate limit, a RateLimitException will be thrown unless automatic handling
     * is enabled via `$client->setRateLimitAutoWait(true)`.
     *
     * @param ProductSheetUpdateDto|array $productSheet The product sheet data to test.
     *                                                   Can be a ProductSheetUpdateDto object or an associative array.
     * @return LogicResultDto The logic test result containing validation status and either errors or logic rules
     * @throws PSApiException If the test request fails due to client or server errors
     * @throws \PSinfoodservice\Exceptions\RateLimitException If rate limit is exceeded
     *
     * @example
     * ```php
     * $productSheet = [
     *     'logistic' => [
     *         'gtin' => '1234567890123',
     *         'name' => [
     *             ['LanguageId' => 18, 'Value' => 'Product Name']
     *         ]
     *     ],
     *     'product' => [
     *         'validfrom' => '2024-01-01T00:00:00'
     *     ]
     * ];
     *
     * try {
     *     $result = $client->validation->testLogic($productSheet);
     *
     *     if ($result->IsValid) {
     *         echo "Validation passed! Logic rules to be applied:\n";
     *         foreach ($result->LogicRules as $rule) {
     *             echo "- " . json_encode($rule) . "\n";
     *         }
     *     } else {
     *         echo "Validation errors:\n";
     *         foreach ($result->Errors as $error) {
     *             echo "- {$error->Position}: {$error->ErrorMessage}\n";
     *         }
     *     }
     * } catch (PSApiException $e) {
     *     echo "API Error: " . $e->getMessage();
     * }
     * ```
     */
    public function testLogic($productSheet): LogicResultDto
    {
        try {
            // Convert ProductSheetUpdateDto to array if needed
            $data = is_array($productSheet) ? $productSheet : json_decode(json_encode($productSheet), true);

            $response = $this->client->getHttpClient()->put(
                $this->client->buildApiPath("validations/logic"),
                [
                    'json' => $data,
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json'
                    ]
                ]
            );

            $responseData = json_decode($response->getBody()->getContents(), true);

            return $this->mapToLogicResult($responseData);

        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);

            // Handle 400 Bad Request with validation errors
            if ($statusCode === 400 && isset($errorResponse['errors'])) {
                return $this->mapToLogicResult([
                    'isValid' => false,
                    'errors' => $errorResponse['errors'],
                    'traceId' => $errorResponse['traceId'] ?? null
                ]);
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

    /**
     * Maps API response data to ValidationResultDto
     *
     * @param array $responseData The API response data
     * @return ValidationResultDto The mapped validation result
     */
    private function mapToValidationResult(array $responseData): ValidationResultDto
    {
        $result = new ValidationResultDto();
        $result->IsValid = $responseData['isValid'] ?? $responseData['IsValid'] ?? false;
        $result->TraceId = $responseData['traceId'] ?? $responseData['TraceId'] ?? null;
        $result->ErrorMessage = $responseData['errorMessage'] ?? $responseData['ErrorMessage'] ?? null;

        // Map header if present
        if (isset($responseData['header']) || isset($responseData['Header'])) {
            $headerData = $responseData['header'] ?? $responseData['Header'];
            $header = new HeaderDto();
            $header->Provider = $headerData['provider'] ?? $headerData['Provider'] ?? 'PS In Foodservice';
            $header->Version = $headerData['version'] ?? $headerData['Version'] ?? '7.0.0.0';
            $header->ActionType = $headerData['actiontype'] ?? $headerData['actionType'] ?? $headerData['ActionType'] ?? 'PUT';
            $header->TraceId = $headerData['traceid'] ?? $headerData['traceiId'] ?? $headerData['TraceId'] ?? $headerData['TraceiId'] ?? null;
            $header->ExecutionTime = $headerData['executiontime'] ?? $headerData['executionTime'] ?? $headerData['ExecutionTime'] ?? '0';
            $result->Header = $header;
        }

        // Map validation errors if present
        if (isset($responseData['errors']) || isset($responseData['Errors'])) {
            $errorsData = $responseData['errors'] ?? $responseData['Errors'];
            if (is_array($errorsData)) {
                $errors = [];
                foreach ($errorsData as $errorData) {
                    $error = new ValidationErrorDto();
                    $error->Position = $errorData['position'] ?? $errorData['Position'] ?? null;
                    $error->ErrorMessage = $errorData['errorMessage'] ?? $errorData['ErrorMessage'] ?? null;
                    $errors[] = $error;
                }
                $result->Errors = $errors;
            }
        }

        return $result;
    }

    /**
     * Maps API response data to LogicResultDto
     *
     * @param array $responseData The API response data
     * @return LogicResultDto The mapped logic result
     */
    private function mapToLogicResult(array $responseData): LogicResultDto
    {
        $result = new LogicResultDto();
        $result->IsValid = $responseData['isValid'] ?? $responseData['IsValid'] ?? false;
        $result->TraceId = $responseData['traceId'] ?? $responseData['TraceId'] ?? null;
        $result->ErrorMessage = $responseData['errorMessage'] ?? $responseData['ErrorMessage'] ?? null;

        // Map header if present
        if (isset($responseData['header']) || isset($responseData['Header'])) {
            $headerData = $responseData['header'] ?? $responseData['Header'];
            $header = new HeaderDto();
            $header->Provider = $headerData['provider'] ?? $headerData['Provider'] ?? 'PS In Foodservice';
            $header->Version = $headerData['version'] ?? $headerData['Version'] ?? '7.0.0.0';
            $header->ActionType = $headerData['actiontype'] ?? $headerData['actionType'] ?? $headerData['ActionType'] ?? 'PUT';
            $header->TraceId = $headerData['traceid'] ?? $headerData['traceiId'] ?? $headerData['TraceId'] ?? $headerData['TraceiId'] ?? null;
            $header->ExecutionTime = $headerData['executiontime'] ?? $headerData['executionTime'] ?? $headerData['ExecutionTime'] ?? '0';
            $result->Header = $header;
        }

        // Map validation errors if present
        if (isset($responseData['errors']) || isset($responseData['Errors'])) {
            $errorsData = $responseData['errors'] ?? $responseData['Errors'];
            if (is_array($errorsData)) {
                $errors = [];
                foreach ($errorsData as $errorData) {
                    $error = new ValidationErrorDto();
                    $error->Position = $errorData['position'] ?? $errorData['Position'] ?? null;
                    $error->ErrorMessage = $errorData['errorMessage'] ?? $errorData['ErrorMessage'] ?? null;
                    $errors[] = $error;
                }
                $result->Errors = $errors;
            }
        }

        // Map logic rules if present
        if (isset($responseData['logicRules']) || isset($responseData['LogicRules'])) {
            $result->LogicRules = $responseData['logicRules'] ?? $responseData['LogicRules'];
        }

        return $result;
    }
}
