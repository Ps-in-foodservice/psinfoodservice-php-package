<?php

declare(strict_types=1);
namespace PSinfoodservice\Services;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException;
use PSinfoodservice\Contracts\BrandServiceInterface;
use PSinfoodservice\Exceptions\PSApiException;
use PSinfoodservice\PSinfoodserviceClient;

/**
 * Service for managing brand-related functionality in the PS in foodservice API.
 */
class BrandService implements BrandServiceInterface
{
    /**
     * @return array{message: string, traceId: string|null}
     */
    private function parseClientErrorResponse(string $body): array
    {
        $decoded = json_decode($body, true);
        if (is_string($decoded) && $decoded !== '') {
            return ['message' => $decoded, 'traceId' => null];
        }
        if (is_array($decoded)) {
            $detail = $decoded['detail'] ?? null;
            $title = $decoded['title'] ?? null;
            $message = is_string($detail) && $detail !== ''
                ? $detail
                : (is_string($title) && $title !== '' ? $title : null);
            if ($message === null) {
                $message = $body !== '' ? $body : 'Unknown error occurred';
            }
            $traceId = $decoded['traceId'] ?? null;
            return [
                'message' => $message,
                'traceId' => is_string($traceId) ? $traceId : null,
            ];
        }

        return [
            'message' => $body !== '' ? $body : 'Unknown error occurred',
            'traceId' => null,
        ];
    }

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
    public function getAll(): ?array
    {
        try {
            $response = $this->client->getHttpClient()->get($this->client->buildApiPath('Brand/All'));
            $data = json_decode($response->getBody()->getContents());

            if (empty($data) || empty($data->brands)) {
                return null;
            }

            return $data->brands;
        } catch (ClientException $e) {
            $parsed = $this->parseClientErrorResponse((string) $e->getResponse()->getBody());
            throw new PSApiException(
                $parsed['message'],
                $e->getResponse()->getStatusCode(),
                $parsed['traceId']
            );
        } catch (ServerException | ConnectException $e) {
            throw new PSApiException($e->getMessage(), 500);
        }
    }

    /**
     * Retrieves all brands added after a specific date.
     *
     * Returns brands that were created on or after the specified date.
     * The date cannot be more than 3 months in the past.
     *
     * **Required Role:** Read, Publish, IsAdministrator, ReadAll, or Sync
     *
     * **Rate Limiting:** 5 requests per second
     *
     * @param \DateTimeInterface|string $fromDate The date from which to retrieve brands.
     * @return array|null An array of brands or null if no brands match
     * @throws PSApiException If retrieval fails or date is more than 3 months in the past
     * @throws \InvalidArgumentException If the date format is invalid
     */
    public function getAllByDate(\DateTimeInterface|string $fromDate): ?array
    {
        try {
            if (is_string($fromDate)) {
                $dateTime = new \DateTime($fromDate);
            } else {
                $dateTime = $fromDate;
            }

            $threeMonthsAgo = new \DateTime('-3 months');
            if ($dateTime < $threeMonthsAgo) {
                throw new PSApiException('FromDate cannot be more than 3 months in the past', 400);
            }

            $formattedDate = $dateTime->format('c');

            $response = $this->client->getHttpClient()->post(
                $this->client->buildApiPath('Brand/all'),
                [
                    'json' => $formattedDate,
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json'
                    ]
                ]
            );

            $data = json_decode($response->getBody()->getContents());

            if (empty($data) || empty($data->brands)) {
                return null;
            }

            return $data->brands;

        } catch (ClientException $e) {
            $parsed = $this->parseClientErrorResponse((string) $e->getResponse()->getBody());
            throw new PSApiException(
                $parsed['message'],
                $e->getResponse()->getStatusCode(),
                $parsed['traceId']
            );
        } catch (ServerException | ConnectException $e) {
            throw new PSApiException($e->getMessage(), 500);
        } catch (\Exception $e) {
            if ($e instanceof PSApiException) {
                throw $e;
            }
            throw new \InvalidArgumentException("Invalid date format: {$e->getMessage()}");
        }
    }

    /**
     * Retrieves all brands associated with the current user.
     *
     * @return array|null An array of user's brands or null if no brands are available
     * @throws PSApiException If retrieval of brands fails
     */
    public function getMyBrands(): ?array
    {
        try {
            $response = $this->client->getHttpClient()->get($this->client->buildApiPath('Brand/MyBrands'));
            $data = json_decode($response->getBody()->getContents());

            if (empty($data) || empty($data->brands)) {
                return null;
            }

            return $data->brands;
        } catch (ClientException $e) {
            $parsed = $this->parseClientErrorResponse((string) $e->getResponse()->getBody());
            throw new PSApiException(
                $parsed['message'],
                $e->getResponse()->getStatusCode(),
                $parsed['traceId']
            );
        } catch (ServerException | ConnectException $e) {
            throw new PSApiException($e->getMessage(), 500);
        }
    }

    /**
     * Creates a new brand or updates an existing brand.
     *
     * @param array $brandData The brand data array
     * @return int The brand ID (newly created or updated)
     * @throws PSApiException If the operation fails
     */
    public function createOrUpdateBrand(array $brandData): int
    {
        try {
            if (!isset($brandData['Name']) || empty($brandData['Name'])) {
                throw new PSApiException('Brand name is required', 400);
            }

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
            $parsed = $this->parseClientErrorResponse((string) $e->getResponse()->getBody());
            $errorMessage = $statusCode === 400
                ? $parsed['message']
                : 'Failed to create or update brand';

            throw new PSApiException(
                $errorMessage,
                $statusCode,
                $parsed['traceId']
            );
        } catch (ServerException | ConnectException $e) {
            throw new PSApiException($e->getMessage(), 500);
        }
    }
}
