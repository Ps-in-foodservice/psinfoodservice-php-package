<?php

declare(strict_types=1);
namespace PSinfoodservice\Domain;

/**
 * Class that provides the base URLs for different PS in foodservice API environments.
 */
class PSFoodServiceUrls
{
    /**
     * Production environment URL.
     */
    private string $production;
    /**
     * Staging environment URL.
     */
    private string $staging;
    /**
     * Test environment URL.
     */
    private string $test;
    /**
     * Development environment URL.
     */
    private string $development;

    /**
     * Initializes a new instance with predefined URLs for each environment.
     */
    public function __construct()
    {        
        $this->development = "https://localhost:5001";
        $this->test = "https://test-api.psinfoodservice.com";
        $this->staging = "https://staging-api.psinfoodservice.com";
        $this->production = "https://production-api.psinfoodservice.com"; 
    }

    /**
     * Returns the base URL for the specified environment.
     *
     * @param string $environment The environment name ('production', 'preproduction')
     * @return string The base URL for the specified environment
     * @throws \InvalidArgumentException If the environment is not valid
     */
    public function getBaseUrl(string $environment): string
    {
        return match ($environment) {
            Environment::production => $this->production,
            Environment::preproduction => $this->staging,
            Environment::test => $this->test,
            Environment::development => $this->development,
            default => throw new \InvalidArgumentException("Invalid environment: {$environment}")
        };
    }
}
