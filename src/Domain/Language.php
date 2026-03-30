<?php
namespace PSinfoodservice\Domain;

/**
 * Class that defines language constants for use in the application.
 */
class Language
{
    /**
     * Supported languages.
     */
    const all = 'all';
    const nl = 'nl';
    const fr = 'fr';
    const en = 'en';
    const de = 'de';

    /**
     * All valid language codes.
     *
     * @var array
     */
    private static $validLanguages = [
        self::all,
        self::nl,
        self::fr,
        self::en,
        self::de
    ];

    /**
     * Checks if the given language code is valid.
     *
     * @param string $language The language code to check
     * @return bool True if the language code is valid, false otherwise
     */
    public static function isValid(string $language): bool
    {
        return in_array($language, self::$validLanguages, true);
    }
    
    /**
     * Returns the default language (Dutch).
     *
     * @return string The default language
     */
    public static function getDefault(): string
    {
        return self::nl;
    }

    /**
     * Attempts to create a valid language code from a string.
     * If the string is not a valid language code, the default language is returned.
     * 
     * @param string $language The language code string
     * @return string A valid language code
     */
    public static function sanitize(string $language): string
    {
        return self::isValid($language) ? $language : self::getDefault();
    }

    /**
     * Throws an exception if the language code is not valid.
     * 
     * @param string $language The language code to validate
     * @return string The validated language code
     * @throws \InvalidArgumentException If the language code is not valid
     */
    public static function validate(string $language): string
    {
        if (!self::isValid($language)) {
            throw new \InvalidArgumentException(
                "Invalid language code: '{$language}'. Valid codes are: " . implode(', ', self::$validLanguages)
            );
        }

        return $language;
    }
}