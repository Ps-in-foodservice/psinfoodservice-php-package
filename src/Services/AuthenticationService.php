<?php

declare(strict_types=1);
namespace PSinfoodservice\Services;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException;
use PSinfoodservice\PSinfoodserviceClient;
use PSinfoodservice\Contracts\AuthenticationServiceInterface;
use PSinfoodservice\Exceptions\PSApiException;

/**
 * Service for handling authentication with the PS in foodservice API.
 */
class AuthenticationService implements AuthenticationServiceInterface
{
    /**
     * Initializes a new instance of the AuthenticationService.
     *
     * @param PSinfoodserviceClient $client The PS in foodservice client
     */
    public function __construct(
        private PSinfoodserviceClient $client
    ) {}

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
            // Clear local tokens after successful server-side logout
            $this->client->clearTokens();
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

    /**
     * Subscribes a webhook URL to receive notifications from the API.
     *
     * When subscribed, your webhook will receive POST requests for relevant events.
     * If a secret is provided, it will be sent in the x-secret header with each webhook call.
     *
     * @param string $webhookUrl The URL where webhook notifications should be sent
     * @param string|null $secret Optional secret that will be sent in the x-secret header
     * @return bool True if subscription was successful
     * @throws PSApiException If subscription fails
     *
     * @example
     * ```php
     * // Subscribe without secret
     * $client->authentication->subscribe('https://your-app.com/webhook');
     *
     * // Subscribe with secret for verification
     * $client->authentication->subscribe(
     *     'https://your-app.com/webhook',
     *     'your-secret-key-123'
     * );
     * ```
     */
    public function subscribe(string $webhookUrl, ?string $secret = null): bool
    {
        try {
            $payload = ['subscribeUrl' => $webhookUrl];

            if ($secret !== null) {
                $payload['secret'] = $secret;
            }

            $this->client->getHttpClient()->post(
                $this->client->buildApiPath('Account/subscribe'),
                ['json' => $payload]
            );

            return true;
        } catch (ClientException $e) {
            $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);
            throw new PSApiException(
                $errorResponse['message'] ?? 'Webhook subscription failed',
                $e->getResponse()->getStatusCode()
            );
        } catch (ServerException $e) {
            throw new PSApiException('Server error', 500);
        } catch (ConnectException $e) {
            throw new PSApiException('Connection failed', 503);
        }
    }

    /**
     * Unsubscribes the previously registered webhook URL.
     *
     * After unsubscribing, your webhook will no longer receive notifications from the API.
     *
     * @return bool True if unsubscription was successful
     * @throws PSApiException If unsubscription fails
     *
     * @example
     * ```php
     * $client->authentication->unsubscribe();
     * ```
     */
    public function unsubscribe(): bool
    {
        try {
            $this->client->getHttpClient()->post(
                $this->client->buildApiPath('Account/unsubscribe')
            );

            return true;
        } catch (ClientException $e) {
            $errorResponse = json_decode($e->getResponse()->getBody()->getContents(), true);
            throw new PSApiException(
                $errorResponse['message'] ?? 'Webhook unsubscription failed',
                $e->getResponse()->getStatusCode()
            );
        } catch (ServerException $e) {
            throw new PSApiException('Server error', 500);
        } catch (ConnectException $e) {
            throw new PSApiException('Connection failed', 503);
        }
    }
}
