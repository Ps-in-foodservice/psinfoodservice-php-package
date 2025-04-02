<?php
namespace PSinfoodservice\Domain;

/**
 * Class that defines environments for use in the application.
 */
class Environment
{
    /**
     * Supported environments.
     */
    //const test = 'test';
    const preproduction = 'preproduction';
    const production = 'production';

    /**
     * All valid environments.
     *
     * @var array
     */
    private static $validEnvironments = [
        //self::test,
        self::preproduction,
        self::production
    ];

    /**
     * Checks if the given environment is valid.
     *
     * @param string $environment The environment to check
     * @return bool True if the environment is valid, false otherwise
     */
    public static function isValid(string $environment): bool
    {
        return in_array($environment, self::$validEnvironments, true);
    }

    /**
     * Returns the default environment (preproduction).
     *
     * @return string The default environment
     */
    public static function getDefault(): string
    {
        return self::preproduction;
    }

    /**
     * Attempts to create a valid environment from a string.
     * If the string is not a valid environment, the default environment is returned.
     * 
     * @param string $environment The environment string
     * @return string A valid environment
     */
    public static function sanitize(string $environment): string
    {
        return self::isValid($environment) ? $environment : self::getDefault();
    }

    /**
     * Throws an exception if the environment is not valid.
     * 
     * @param string $environment The environment to validate
     * @return string The validated environment
     * @throws \InvalidArgumentException If the environment is not valid
     */
    public static function validate(string $environment): string
    {
        if (!self::isValid($environment)) {
            throw new \InvalidArgumentException(
                "Invalid environment: '{$environment}'. Valid environments are: " . implode(', ', self::$validEnvironments)
            );
        }
        return $environment;
    }
}