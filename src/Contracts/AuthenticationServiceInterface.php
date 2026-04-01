<?php

declare(strict_types=1);
namespace PSinfoodservice\Contracts;

use PSinfoodservice\Exceptions\PSApiException;

/**
 * Interface for authentication service operations.
 */
interface AuthenticationServiceInterface
{
    /**
     * Authenticates with the API using username and password.
     *
     * @param string $username The username
     * @param string $password The password
     * @return bool True if authentication was successful
     * @throws PSApiException If authentication fails
     */
    public function login(string $username, string $password): bool;

    /**
     * Refreshes the access token using the refresh token.
     *
     * @param string $accessToken The current access token
     * @param string $refreshToken The refresh token
     * @return bool True if refresh was successful
     * @throws PSApiException If token refresh fails
     */
    public function refreshToken(string $accessToken, string $refreshToken): bool;

    /**
     * Logs out of the API, invalidating the current token.
     *
     * @throws PSApiException If logout fails
     */
    public function logoff(): void;

    /**
     * Subscribes a webhook URL to receive notifications from the API.
     *
     * @param string $webhookUrl The URL where webhook notifications should be sent
     * @param string|null $secret Optional secret for the x-secret header
     * @return bool True if subscription was successful
     * @throws PSApiException If subscription fails
     */
    public function subscribe(string $webhookUrl, ?string $secret = null): bool;

    /**
     * Unsubscribes the previously registered webhook URL.
     *
     * @return bool True if unsubscription was successful
     * @throws PSApiException If unsubscription fails
     */
    public function unsubscribe(): bool;
}
