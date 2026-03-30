<?php
namespace PSinfoodservice\Domain;

/**
* Class that defines output styles for use in the application.
 */
class Outputstyle
{
    /**
     * Supported styles.
     */
    const table = 'table';
    const bootstrap = 'bootstrap';

    /**
     * All valid styles.
     *
     * @var array
     */
    private static $validStyles = [
        self::table,
        self::bootstrap
    ];

    /**
     * Checks if the given style is valid.
     *
     * @param string $style The style to check
     * @return bool True if the style is valid, false otherwise
     */
    public static function isValid(string $style): bool
    {
        return in_array($style, self::$validStyles, true);
    }

    /**
     * Returns the default style (table).
     *
     * @return string The default style
     */
    public static function getDefault(): string
    {
        return self::table;
    }

    /**
     * Attempts to create a valid style from a string.
     * If the string is not a valid style, the default style is returned.
     * 
     * @param string $style The style string
     * @return string A valid style
     */
    public static function sanitize(string $style): string
    {
        return self::isValid($style) ? $style : self::getDefault();
    }

    /**
     * Throws an exception if the style is not valid.
     * 
     * @param string $style The style to validate
     * @return string The validated style
     * @throws \InvalidArgumentException If the style is not valid
     */
    public static function validate(string $style): string
    {
        if (!self::isValid($style)) {
            throw new \InvalidArgumentException(
                "Invalid style: '{$style}'. Valid codes are: " . implode(', ', self::$validStyles)
            );
        }

        return $style;
    }
}