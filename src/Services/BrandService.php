<?php

declare(strict_types=1);
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
     * Initializes a new instance of the BrandService.
     *
     * @param PSinfoodserviceClient $client The PS in foodservice client
     */
    public function __construct(
        private PSinfoodserviceClient $client
    ) {}

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

    /**
     * Creates a new brand or updates an existing brand.
     *
     * This method sends a POST request to create or update a brand. Only users with the
     * Producer type can use this endpoint. When creating a new brand, set the 'Id' to 0.
     * When updating, provide the existing brand ID and ensure you are the brand owner.
     *
     * **Required Role:** Publish
     * **Required Type:** Producer
     *
     * **Rate Limiting:** 5 requests per second
     *
     * @param array $brandData The brand data array with the following structure:
     *   - Id: int (0 for new brand, existing ID for update)
     *   - BrandOwnerId: int (optional, the relation ID of the brand owner)
     *   - Name: string (required, brand name)
     *   - Description: string (optional, brand description)
     *   - Website: string (optional, brand website URL)
     *   - Email: string (optional, contact email)
     *   - Telephone: string (optional, contact phone number)
     *   - DeclarationFormatTypeId: int (optional, format type for declarations)
     *   - IsVisibleInProducerDetail: bool (optional, visibility in producer details)
     *   - IsPrivateLabel: bool (optional, whether this is a private label)
     *   - IsPubliclyVisible: bool (optional, public visibility)
     *   - AllowProducersToPublishSpecification: bool (optional, allow producers to publish specs)
     *   - IngredientPercentageInFront: bool (optional, show ingredient percentage upfront)
     *   - ProducerMustApproveSpecification: bool (optional, require producer approval)
     *   - LabelContacts: array (optional, array of label contact information)
     *
     * @return int The brand ID (newly created or updated)
     * @throws PSApiException If the operation fails or user is not a producer/brand owner
     *
     * @example
     * ```php
     * // Create a new brand
     * $brandData = [
     *     'Id' => 0,
     *     'Name' => 'My Brand',
     *     'Description' => 'Premium quality products',
     *     'Website' => 'https://mybrand.com',
     *     'Email' => 'info@mybrand.com',
     *     'IsPubliclyVisible' => true
     * ];
     *
     * try {
     *     $brandId = $client->brands->createOrUpdateBrand($brandData);
     *     echo "Brand created with ID: {$brandId}";
     * } catch (PSApiException $e) {
     *     if ($e->getStatusCode() === 400) {
     *         echo "Error: " . $e->getMessage();
     *         // Possible errors:
     *         // - "You are not a producer"
     *         // - "You are not the owner of this brand"
     *     }
     * }
     *
     * // Update an existing brand
     * $updateData = [
     *     'Id' => 123,
     *     'Name' => 'My Updated Brand',
     *     'Description' => 'New description',
     *     'Website' => 'https://newsite.com'
     * ];
     *
     * $brandId = $client->brands->createOrUpdateBrand($updateData);
     * echo "Brand updated: {$brandId}";
     *
     * // Create brand with label contacts
     * $brandWithContacts = [
     *     'Id' => 0,
     *     'Name' => 'Brand with Contacts',
     *     'LabelContacts' => [
     *         [
     *             'Name' => 'Contact Person',
     *             'TargetMarketId' => 18, // Netherlands
     *             'CommunicationAddress' => '123 Street Name, City',
     *             'CommunicationChannels' => [
     *                 ['Type' => 'email', 'Value' => 'contact@brand.com']
     *             ]
     *         ]
     *     ]
     * ];
     *
     * $brandId = $client->brands->createOrUpdateBrand($brandWithContacts);
     * ```
     */
    public function createOrUpdateBrand(array $brandData): int
    {
        try {
            // Ensure required fields are present
            if (!isset($brandData['Name']) || empty($brandData['Name'])) {
                throw new PSApiException('Brand name is required', 400);
            }

            // Set Id to 0 if not provided (for new brands)
            if (!isset($brandData['Id'])) {
                $brandData['Id'] = 0;
            }

            $response = $this->client->getHttpClient()->post(
                $this->client->buildApiPath('Brand/brand'),
                [
                    'json' => $brandData,
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json'
                    ]
                ]
            );

            $brandId = json_decode($response->getBody()->getContents(), true);

            return (int)$brandId;

        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);

            // Handle specific error cases
            $errorMessage = 'Failed to create or update brand';

            if ($statusCode === 400) {
                // Check for specific error messages
                if (is_string($errorResponse)) {
                    $errorMessage = $errorResponse;
                } elseif (isset($errorResponse['detail'])) {
                    $errorMessage = $errorResponse['detail'];
                } elseif (isset($errorResponse['title'])) {
                    $errorMessage = $errorResponse['title'];
                }
            }

            throw new PSApiException(
                $errorMessage,
                $statusCode,
                $errorResponse['traceId'] ?? null
            );
        } catch (ServerException | ConnectException $e) {
            throw new PSApiException($e->getMessage(), 500);
        }
    }
}
