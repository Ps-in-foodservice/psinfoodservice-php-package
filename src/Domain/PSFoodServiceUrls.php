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
     * Initializes a new instance with predefined URLs for each environment.
     */
    public function __construct()
    {
        $this->staging = "https://staging-api.psinfoodservice.com";
        $this->production = "https://webapi.psinfoodservice.com";
    }

    /**
     * Returns the base URL for the specified environment.
     *
     * @param string $environment The environment name ('production', 'staging')
     * @return string The base URL for the specified environment
     * @throws \InvalidArgumentException If the environment is not valid
     */
    public function getBaseUrl(string $environment): string
    {
        return match ($environment) {
            Environment::production => $this->production,
            Environment::staging => $this->staging,
            default => throw new \InvalidArgumentException("Invalid environment: {$environment}")
        };
    }
}
