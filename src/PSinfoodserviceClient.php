<?php
namespace PSinfoodservice;
 
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\RequestOptions;
use PSinfoodservice\Domain\PSFoodServiceUrls;
use PSinfoodservice\Domain\Environment;
use PSinfoodservice\Middleware\RetryMiddleware;
use PSinfoodservice\Middleware\RateLimitMiddleware;
use PSinfoodservice\Services\AuthenticationService;
use PSinfoodservice\Services\FileService;
use PSinfoodservice\Services\WebApiService;
use PSinfoodservice\Services\AssortmentService;
use PSinfoodservice\Services\ImpactScoreService;
use PSinfoodservice\Services\LookupService;
use PSinfoodservice\Services\MasterService;
use PSinfoodservice\Services\BrandService;
use PSinfoodservice\Services\HelperService;
use Psr\Log\LoggerInterface;

/**
 * Main client class for interacting with the PS in foodservice API
 * 
 * This client provides access to all API endpoints through service-specific
 * modules and handles authentication, request formatting, and response handling.
 */
class PSinfoodserviceClient
{
    /**
     * Base URL for API requests
     * 
     * @var string
     */
    private string $baseUrl;

    /**
     * Guzzle HTTP client instance
     * 
     * @var Client
     */
    private Client $httpClient;

    /**
     * Current access token for authenticated requests
     * 
     * @var string
     */
    private string $accessToken;

    /**
     * Refresh token used to obtain new access tokens
     * 
     * @var string
     */
    private string $refreshToken;

    /**
     * Token expiration time in seconds
     * 
     * @var int
     */
    private int $expiresIn;

    /**
     * API version prefix path, e.g., "/v7/json"
     *
     * @var string
     */
    private string $apiPrefix = '/v7/json';

    /**
     * Whether to verify SSL certificates in HTTPS requests
     *
     * @var bool
     */
    private bool $verifySSL;

    /**
     * Timestamp when the access token was obtained (Unix timestamp)
     *
     * @var int|null
     */
    private ?int $tokenObtainedAt = null;

    /**
     * Whether automatic token refresh is enabled
     *
     * @var bool
     */
    private bool $autoRefreshEnabled = true;

    /**
     * Safety margin in seconds before token expiry to trigger refresh
     * Default: 60 seconds
     *
     * @var int
     */
    private int $tokenRefreshMargin = 60;

    /**
     * Maximum number of retry attempts for failed requests
     * Default: 3
     *
     * @var int
     */
    private int $maxRetries = 3;

    /**
     * Base delay in milliseconds for retry backoff
     * Default: 1000 (1 second)
     *
     * @var int
     */
    private int $retryDelay = 1000;

    /**
     * Whether retry functionality is enabled
     * Default: true
     *
     * @var bool
     */
    private bool $retryEnabled = true;

    /**
     * Optional PSR-3 logger for logging retry attempts
     *
     * @var LoggerInterface|null
     */
    private ?LoggerInterface $logger = null;

    /**
     * Whether to automatically wait and retry on rate limit (429 responses)
     * Default: false
     *
     * @var bool
     */
    private bool $rateLimitAutoWait = false;

    /**
     * Maximum time in seconds to wait for rate limit auto-retry
     * Default: 60 seconds
     *
     * @var int
     */
    private int $rateLimitMaxWait = 60;

    /**
     * Authentication service for login and token management
     *
     * @var AuthenticationService
     */
    public AuthenticationService $authentication;

    /**
     * Web API service for core product operations
     * 
     * @var WebApiService
     */
    public WebApiService $webApi;

    /**
     * Impact Score service for environmental impact data
     * 
     * @var ImpactScoreService
     */
    public ImpactScoreService $impactScore;

    /**
     * File service for file and image retrieval
     *
     * @var FileService
     */
    public FileService $files;

    /**
     * Assortment service for managing assortment lists
     * 
     * @var AssortmentService
     */
    public AssortmentService $assortment;

    /**
     * Lookup service for tracking product changes
     * 
     * @var LookupService
     */
    public LookupService $lookups;

    /**
     * Master service for reference data management
     * 
     * @var MasterService
     */
    public MasterService $masters;

    /**
     * Brand service for brand information
     * 
     * @var BrandService
     */
    public BrandService $brands;

    /**
     * Helper service with utility methods for data processing
     * 
     * @var HelperService
     */
    public HelperService $helper;
     
    /**
     * Initialize the PS in foodservice API client
     *
     * Creates a new client instance and initializes all service modules.
     *
     * @param string $environment The API environment to use (preproduction or production)
     * @param string|null $apiPrefix Optional API version prefix (defaults to '/v7/json')
     * @param bool $verifySSL Whether to verify SSL certificates (default: true)
     *                        WARNING: Only set to false for development/testing environments.
     *                        Disabling SSL verification in production is a security risk.
     * @param bool $autoRefreshEnabled Whether to automatically refresh expired tokens (default: true)
     * @param array $retryConfig Optional retry configuration:
     *                          - 'enabled' (bool): Enable retry logic (default: true)
     *                          - 'max_retries' (int): Maximum retry attempts (default: 3)
     *                          - 'retry_delay' (int): Base delay in ms (default: 1000)
     *                          - 'logger' (LoggerInterface): Optional PSR-3 logger
     *                          - 'rate_limit_auto_wait' (bool): Auto-wait on rate limit (default: false)
     *                          - 'rate_limit_max_wait' (int): Max seconds to wait (default: 60)
     */
    public function __construct(
        string $environment = Environment::preproduction,
        ?string $apiPrefix = null,
        bool $verifySSL = true,
        bool $autoRefreshEnabled = true,
        array $retryConfig = []
    )
    {
        $urls = new PSFoodServiceUrls();
        $this->baseUrl = $urls->getBaseUrl($environment);

        // Determine API prefix: param > env var > default
        $prefixFromEnv = getenv('PS_API_PREFIX') ?: null;
        $this->apiPrefix = rtrim($apiPrefix ?? $prefixFromEnv ?? '/v7/json', '/');

        $this->verifySSL = $verifySSL;
        $this->autoRefreshEnabled = $autoRefreshEnabled;

        // Configure retry settings
        $this->retryEnabled = $retryConfig['enabled'] ?? true;
        $this->maxRetries = $retryConfig['max_retries'] ?? 3;
        $this->retryDelay = $retryConfig['retry_delay'] ?? 1000;
        $this->logger = $retryConfig['logger'] ?? null;

        // Configure rate limit settings
        $this->rateLimitAutoWait = $retryConfig['rate_limit_auto_wait'] ?? false;
        $this->rateLimitMaxWait = $retryConfig['rate_limit_max_wait'] ?? 60;

        // Create HTTP client with retry middleware
        $this->httpClient = $this->createHttpClient();

        $this->authentication = new AuthenticationService($this);
        $this->webApi = new WebApiService($this);
        $this->impactScore = new ImpactScoreService($this);
        $this->assortment = new AssortmentService($this);
        $this->lookups = new LookupService($this);
        $this->masters = new MasterService($this);
        $this->brands = new BrandService($this);
        $this->files = new FileService($this);
        $this->helper = new HelperService(); 
    }

    /**
     * Get the current API prefix path (e.g., "/v7/json").
     */
    public function getApiPrefix(): string
    {
        return $this->apiPrefix;
    }

    /**
     * Set the API prefix path (e.g., "/v7/json").
     */
    public function setApiPrefix(string $apiPrefix): void
    {
        $this->apiPrefix = rtrim($apiPrefix, '/');
    }

    /**
     * Build a versioned API path by prepending the API prefix.
     */
    public function buildApiPath(string $path): string
    {
        $path = '/' . ltrim($path, '/');
        return $this->apiPrefix . $path;
    }

    /**
     * Create an HTTP client with configured middleware
     *
     * @param string|null $authToken Optional authorization token
     * @return Client
     */
    private function createHttpClient(?string $authToken = null): Client
    {
        // Create handler stack with retry middleware
        $stack = HandlerStack::create();

        if ($this->retryEnabled) {
            $retryMiddleware = new RetryMiddleware(
                $this->maxRetries,
                $this->retryDelay,
                [500, 502, 503, 504], // Retry status codes
                $this->logger
            );
            $stack->push($retryMiddleware);
        }

        // Add rate limit middleware (pushed last = executed first)
        $rateLimitMiddleware = new RateLimitMiddleware(
            $this->rateLimitAutoWait,
            $this->rateLimitMaxWait,
            $this->logger
        );
        $stack->push($rateLimitMiddleware);

        // Build headers
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ];

        if ($authToken !== null) {
            $headers['Authorization'] = "Bearer {$authToken}";
        }

        return new Client([
            'base_uri' => $this->baseUrl,
            'verify' => $this->verifySSL,
            'handler' => $stack,
            RequestOptions::HEADERS => $headers
        ]);
    }

    /**
     * Set the access token for authenticated requests
     *
     * Updates the HTTP client with the new token in the Authorization header
     * and records the timestamp when the token was obtained.
     *
     * @param string $token The access token received from authentication
     * @return void
     */
    public function setAccessToken(string $token): void
    {
        $this->accessToken = $token;
        $this->tokenObtainedAt = time();
        $this->httpClient = $this->createHttpClient($token);
    }

    /**
     * Get the current HTTP client instance
     * 
     * @return Client The configured Guzzle HTTP client
     */
    public function getHttpClient(): Client
    {
        return $this->httpClient;
    }

    /**
     * Set the refresh token for token renewal
     * 
     * @param string $token The refresh token received from authentication
     * @return void
     */
    public function setRefreshToken(string $token): void
    {
        $this->refreshToken = $token;
    }
     
    /**
     * Set the token expiration time
     *
     * @param int $expiresIn Token lifetime in seconds
     * @return void
     */
    public function setExpiresIn(int $expiresIn): void
    {
        $this->expiresIn = $expiresIn;
    }

    /**
     * Get the current access token
     *
     * @return string|null The current access token or null if not set
     */
    public function getAccessToken(): ?string
    {
        return $this->accessToken ?? null;
    }

    /**
     * Get the current refresh token
     *
     * @return string|null The current refresh token or null if not set
     */
    public function getRefreshToken(): ?string
    {
        return $this->refreshToken ?? null;
    }

    /**
     * Get the token expiration time in seconds
     *
     * @return int|null The token lifetime in seconds or null if not set
     */
    public function getExpiresIn(): ?int
    {
        return $this->expiresIn ?? null;
    }

    /**
     * Get the timestamp when the token was obtained
     *
     * @return int|null Unix timestamp or null if token hasn't been set
     */
    public function getTokenObtainedAt(): ?int
    {
        return $this->tokenObtainedAt;
    }

    /**
     * Check if the current access token is expired or about to expire
     *
     * Considers the token expired if:
     * - No token has been set
     * - Current time exceeds (token obtained time + expiry duration - safety margin)
     *
     * @param int|null $margin Optional custom safety margin in seconds (default: uses configured margin)
     * @return bool True if the token is expired or about to expire
     */
    public function isTokenExpired(?int $margin = null): bool
    {
        // If no token has been set yet, consider it expired
        if ($this->tokenObtainedAt === null || !isset($this->expiresIn)) {
            return true;
        }

        $safetyMargin = $margin ?? $this->tokenRefreshMargin;
        $expiryTime = $this->tokenObtainedAt + $this->expiresIn - $safetyMargin;

        return time() >= $expiryTime;
    }

    /**
     * Ensure the current token is valid, refreshing it if necessary
     *
     * This method checks if the current token is expired or about to expire.
     * If automatic refresh is enabled and the token needs refreshing, it will
     * automatically call the refresh token endpoint to obtain a new token.
     *
     * @return void
     * @throws \PSinfoodservice\Exceptions\PSApiException If token refresh fails
     *
     * @example
     * ```php
     * // Manually ensure token is valid before making an API call
     * $client->ensureValidToken();
     * $products = $client->webApi->getMyProducts();
     * ```
     */
    public function ensureValidToken(): void
    {
        // Skip if auto-refresh is disabled
        if (!$this->autoRefreshEnabled) {
            return;
        }

        // Skip if token is still valid
        if (!$this->isTokenExpired()) {
            return;
        }

        // Check if we have the required tokens for refresh
        if (!isset($this->accessToken) || !isset($this->refreshToken)) {
            throw new \PSinfoodservice\Exceptions\PSApiException(
                'Cannot refresh token: access token or refresh token is not set. Please login first.',
                401
            );
        }

        // Attempt to refresh the token
        try {
            $this->authentication->refreshToken($this->accessToken, $this->refreshToken);
        } catch (\PSinfoodservice\Exceptions\PSApiException $e) {
            // Re-throw with more context
            throw new \PSinfoodservice\Exceptions\PSApiException(
                'Automatic token refresh failed: ' . $e->getMessage() . '. Please login again.',
                $e->getStatusCode(),
                $e->getTraceId()
            );
        }
    }

    /**
     * Enable or disable automatic token refresh
     *
     * @param bool $enabled True to enable, false to disable
     * @return void
     */
    public function setAutoRefreshEnabled(bool $enabled): void
    {
        $this->autoRefreshEnabled = $enabled;
    }

    /**
     * Check if automatic token refresh is enabled
     *
     * @return bool True if enabled, false otherwise
     */
    public function isAutoRefreshEnabled(): bool
    {
        return $this->autoRefreshEnabled;
    }

    /**
     * Set the safety margin for token refresh
     *
     * The token will be considered expired this many seconds before actual expiry.
     * This provides a buffer to prevent making API calls with an about-to-expire token.
     *
     * @param int $seconds Safety margin in seconds (default: 60)
     * @return void
     */
    public function setTokenRefreshMargin(int $seconds): void
    {
        $this->tokenRefreshMargin = max(0, $seconds);
    }

    /**
     * Get the current token refresh safety margin
     *
     * @return int Safety margin in seconds
     */
    public function getTokenRefreshMargin(): int
    {
        return $this->tokenRefreshMargin;
    }

    /**
     * Enable or disable retry logic
     *
     * @param bool $enabled True to enable, false to disable
     * @return void
     */
    public function setRetryEnabled(bool $enabled): void
    {
        if ($this->retryEnabled !== $enabled) {
            $this->retryEnabled = $enabled;
            // Recreate HTTP client with new retry settings
            $this->httpClient = $this->createHttpClient($this->accessToken ?? null);
        }
    }

    /**
     * Check if retry logic is enabled
     *
     * @return bool True if enabled, false otherwise
     */
    public function isRetryEnabled(): bool
    {
        return $this->retryEnabled;
    }

    /**
     * Set the maximum number of retry attempts
     *
     * @param int $maxRetries Maximum retry attempts (0 to disable retries)
     * @return void
     */
    public function setMaxRetries(int $maxRetries): void
    {
        $this->maxRetries = max(0, $maxRetries);
        if ($this->retryEnabled) {
            // Recreate HTTP client with new retry settings
            $this->httpClient = $this->createHttpClient($this->accessToken ?? null);
        }
    }

    /**
     * Get the maximum number of retry attempts
     *
     * @return int Maximum retry attempts
     */
    public function getMaxRetries(): int
    {
        return $this->maxRetries;
    }

    /**
     * Set the base delay for retry backoff in milliseconds
     *
     * @param int $delayMs Base delay in milliseconds
     * @return void
     */
    public function setRetryDelay(int $delayMs): void
    {
        $this->retryDelay = max(0, $delayMs);
        if ($this->retryEnabled) {
            // Recreate HTTP client with new retry settings
            $this->httpClient = $this->createHttpClient($this->accessToken ?? null);
        }
    }

    /**
     * Get the base delay for retry backoff
     *
     * @return int Base delay in milliseconds
     */
    public function getRetryDelay(): int
    {
        return $this->retryDelay;
    }

    /**
     * Set the PSR-3 logger for logging retry attempts
     *
     * @param LoggerInterface|null $logger PSR-3 logger instance or null to disable logging
     * @return void
     */
    public function setLogger(?LoggerInterface $logger): void
    {
        $this->logger = $logger;
        if ($this->retryEnabled) {
            // Recreate HTTP client with new logger
            $this->httpClient = $this->createHttpClient($this->accessToken ?? null);
        }
    }

    /**
     * Get the current logger instance
     *
     * @return LoggerInterface|null Current logger or null if not set
     */
    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Enable or disable automatic wait-and-retry on rate limit (429 responses)
     *
     * When enabled, the client will automatically wait for the duration specified
     * in the Retry-After header (up to maxWaitTime) and retry the request.
     *
     * @param bool $enabled True to enable auto-wait, false to throw RateLimitException immediately
     * @return void
     */
    public function setRateLimitAutoWait(bool $enabled): void
    {
        if ($this->rateLimitAutoWait !== $enabled) {
            $this->rateLimitAutoWait = $enabled;
            // Recreate HTTP client with new rate limit settings
            $this->httpClient = $this->createHttpClient($this->accessToken ?? null);
        }
    }

    /**
     * Check if automatic wait-and-retry on rate limit is enabled
     *
     * @return bool True if enabled, false otherwise
     */
    public function isRateLimitAutoWaitEnabled(): bool
    {
        return $this->rateLimitAutoWait;
    }

    /**
     * Set the maximum time to wait for rate limit auto-retry
     *
     * If the Retry-After header specifies a wait time longer than this,
     * a RateLimitException will be thrown instead of waiting.
     *
     * @param int $seconds Maximum seconds to wait (default: 60)
     * @return void
     */
    public function setRateLimitMaxWait(int $seconds): void
    {
        $this->rateLimitMaxWait = max(1, $seconds);
        // Recreate HTTP client with new rate limit settings
        $this->httpClient = $this->createHttpClient($this->accessToken ?? null);
    }

    /**
     * Get the maximum wait time for rate limit auto-retry
     *
     * @return int Maximum seconds to wait
     */
    public function getRateLimitMaxWait(): int
    {
        return $this->rateLimitMaxWait;
    }
}