<?php
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
     * Pre-production environment URL.
     */
    private string $preProduction;
    /**
     * Test environment URL.
     */
    private string $test;

    /**
     * Initializes a new instance with predefined URLs for each environment.
     */
    public function __construct()
    {        
        $this->test = "https://localhost:5001/v7";
        $this->production = "https://webapi.psinfoodservice.com/v7";
        $this->preProduction = "https://webapi.prepod.psinfoodservice.com/v7";
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
        switch ($environment) {
            case Environment::production:
                return $this->production;
            case Environment::preproduction:
                return $this->preProduction;
            case Environment::test:
                return $this->test;
            default:
                throw new \InvalidArgumentException("Invalid environment: {$environment}");
        }
    }
}