<?php
namespace PSinfoodservice\Domain;

/**
 * Class that defines output for use in the application.
 */
class Output
{
    /**
     * Supported outputs.
     */
    const all = 'All';
    const summary = 'Summary';
    const productcontent = 'ProductContent';
    const logistics = 'Logistics';

    /**
     * All valid outputs.
     *
     * @var array
     */
    private static $validOuputs = [
        self::all,
        self::summary,
        self::productcontent,
        self::logistics
    ];

    /**
     * Checks if the given output is valid.
     *
     * @param string $output The output to check
     * @return bool True if the output is valid, false otherwise
     */
    public static function isValid(string $output): bool
    {
        return in_array($output, self::$validOuputs, true);
    }

    /**
     * Returns the default output (all).
     *
     * @return string The default output
     */
    public static function getDefault(): string
    {
        return self::all;
    }

    /**
     * Attempts to create a valid output from a string.
     * If the string is not a valid output, the default output is returned.
     * 
     * @param string $output The output string
     * @return string A valid output
     */
    public static function sanitize(string $output): string
    {
        return self::isValid($output) ? $output : self::getDefault();
    }

    /**
     * Throws an exception if the output is not valid.
     * 
     * @param string $output The output to validate
     * @return string The validated output
     * @throws \InvalidArgumentException If the output is not valid
     */
    public static function validate(string $output): string
    {
        if (!self::isValid($output)) {
            throw new \InvalidArgumentException(
                "Invalid output: '{$output}'. Valid outputs are: " . implode(', ', self::$validOuputs)
            );
        }

        return $output;
    }
}