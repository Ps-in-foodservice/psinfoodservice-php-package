<?php
namespace PSinfoodservice\Services;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException;
use PSinfoodservice\Exceptions\PSApiException;
use PSinfoodservice\PSinfoodserviceClient;

/**
 * Service for handling product image operations in the PS in foodservice API.
 */
class ImageService
{
    /**
     * The PS in foodservice client instance.
     */
    private PSinfoodserviceClient $client;

    /**
     * Initializes a new instance of the ImageService.
     *
     * @param PSinfoodserviceClient $client The PS in foodservice client
     */
    public function __construct(PSinfoodserviceClient $client)
    {
        $this->client = $client;
    }

    /**
     * Retrieves an image from the API with specified dimensions.
     *
     * @param int $fileId The ID of the image file
     * @param string $securityToken The security token for accessing the image
     * @param int $width The desired width of the image (default: 500)
     * @param int $height The desired height of the image (default: 500)
     * @return string|null The image data as string or null if no data is available
     * @throws PSApiException If retrieval of the image fails
     */
    public function getImage(int $fileId, string $securityToken, int $width = 500, int $height = 500): string
    {
        try {
            $response = $this->client->getHttpClient()->get("/Image/{$fileId}/{$securityToken}", [
                'query' => [
                    'width' => $width,
                    'height' => $height
                ],
                'headers' => [
                    'Accept' => 'image/*'
                ]
            ]);
            $data = $response->getBody()->getContents();

            if (!$data) {
                return null;
            }

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