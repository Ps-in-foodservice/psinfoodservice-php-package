<?php
namespace PSinfoodservice\Services;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException;
use PSinfoodservice\PSinfoodserviceClient;
use PSinfoodservice\Exceptions\PSApiException;

/**
 * Service for handling authentication with the PS in foodservice API.
 */
class AuthenticationService
{
    /**
     * The PS in foodservice client instance.
     */
    private PSinfoodserviceClient $client;

    /**
     * Initializes a new instance of the AuthenticationService.
     *
     * @param PSinfoodserviceClient $client The PS in foodservice client
     */
    public function __construct(PSinfoodserviceClient $client)
    { 
        $this->client = $client;
    }

    /**
     * Authenticates with the API using username and password.
     *
     * @param string $username The username
     * @param string $password The password
     * @return bool True if authentication was successful
     * @throws PSApiException If authentication fails
     */
    public function login(string $username, string $password): bool
    {
        try {
            $response = $this->client->getHttpClient()->post($this->client->buildApiPath('Account/Login'), [
                'json' => [
                    'username' => $username,
                    'password' => $password
                ]
            ]);

            $loginResponse = json_decode($response->getBody()->getContents(), true);

             //Store tokens
            $this->client->setAccessToken($loginResponse['accesstoken']);
            $this->client->setRefreshToken($loginResponse['refreshtoken']);
            $this->client->setExpiresIn($loginResponse['expiresin']);

            return true;
        } catch (ClientException $e) {
            $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);
            throw new PSApiException(
                $errorResponse['message'] ?? 'Login failed',
                $e->getResponse()->getStatusCode()
            );
        } catch (ServerException $e) {
            throw new PSApiException('Server error', 500);
        } catch (ConnectException $e) {
            throw new PSApiException('Connection failed', 503);
        }
    }

    /**
     * Refreshes the access token using the refresh token.
     *
     * @param string $accessToken The current access token
     * @param string $refreshToken The refresh token
     * @return bool True if refresh was successful
     * @throws PSApiException If token refresh fails
     */
    public function refreshToken(string $accessToken, string $refreshToken): bool
    {
        try {
            $response = $this->client->getHttpClient()->post($this->client->buildApiPath('Account/RefreshToken'), [
                'json' => [
                    'accesstoken' => $accessToken,
                    'refreshtoken' => $refreshToken
                ]
            ]);
            $refreshResponse = json_decode($response->getBody()->getContents(), true);
            
            // Store tokens
            $this->client->setAccessToken($refreshResponse['accessToken']);
            $this->client->setRefreshToken($refreshResponse['refreshToken']);
            $this->client->setExpiresIn($refreshResponse['expiresIn']);
            return true;
        } catch (ClientException $e) {
            $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);
            throw new PSApiException(
                $errorResponse['message'] ?? 'Token refresh failed',
                $e->getResponse()->getStatusCode()
            );
        } catch (ServerException $e) {
            throw new PSApiException('Server error', 500);
        } catch (ConnectException $e) {
            throw new PSApiException('Connection failed', 503);
        }
    }

    /**
     * Logs out of the API, invalidating the current token.
     *
     * @throws PSApiException If logout fails
     */
    public function logoff(): void
    {
        try {
            $this->client->getHttpClient()->post($this->client->buildApiPath('Account/logout'));
        } catch (ClientException $e) {
            $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);
            throw new PSApiException(
                $errorResponse['message'] ?? 'Logoff failed',
                $e->getResponse()->getStatusCode()
            );
        } catch (ServerException $e) {
            throw new PSApiException('Server error', 500);
        } catch (ConnectException $e) {
            throw new PSApiException('Connection failed', 503);
        }
    }
}