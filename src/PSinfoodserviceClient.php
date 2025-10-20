<?php
namespace PSinfoodservice;
 
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use PSinfoodservice\Domain\PSFoodServiceUrls;
use PSinfoodservice\Domain\Environment;
use PSinfoodservice\Services\AuthenticationService;
use PSinfoodservice\Services\ImageService;
use PSinfoodservice\Services\WebApiService;
use PSinfoodservice\Services\AssortmentService;
use PSinfoodservice\Services\ImpactScoreService;
use PSinfoodservice\Services\LookupService;
use PSinfoodservice\Services\MasterService;
use PSinfoodservice\Services\BrandService;
use PSinfoodservice\Services\HelperService;

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
     * Image service for product image retrieval
     * 
     * @var ImageService
     */
    public ImageService $images;

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
     */
    public function __construct(string $environment = Environment::preproduction, ?string $apiPrefix = null)
    {
        $urls = new PSFoodServiceUrls();
        $this->baseUrl = $urls->getBaseUrl($environment);

        // Determine API prefix: param > env var > default
        $prefixFromEnv = getenv('PS_API_PREFIX') ?: null;
        $this->apiPrefix = rtrim($apiPrefix ?? $prefixFromEnv ?? '/v7/json', '/');

        $this->httpClient = new Client([
            'base_uri' => $this->baseUrl,
            'verify' => false,
            RequestOptions::HEADERS => [ 
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ]
        ]);

        $this->authentication = new AuthenticationService($this);
        $this->webApi = new WebApiService($this);
        $this->impactScore = new ImpactScoreService($this);
        $this->assortment = new AssortmentService($this);
        $this->lookups = new LookupService($this);
        $this->masters = new MasterService($this);
        $this->brands = new BrandService($this);
        $this->images = new ImageService($this); 
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
     * Set the API prefix path (e.g., "/v8/json").
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
     * Set the access token for authenticated requests
     * 
     * Updates the HTTP client with the new token in the Authorization header.
     * 
     * @param string $token The access token received from authentication
     * @return void
     */
    public function setAccessToken(string $token): void
    {
        $this->accessToken = $token;
        $this->httpClient = new Client([
            'base_uri' => $this->baseUrl,
            'verify' => false,
            RequestOptions::HEADERS => [
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ]
        ]);
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
}