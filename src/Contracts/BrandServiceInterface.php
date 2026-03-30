<?php

declare(strict_types=1);
namespace PSinfoodservice\Contracts;

use PSinfoodservice\Exceptions\PSApiException;

/**
 * Interface for brand service operations.
 */
interface BrandServiceInterface
{
    /**
     * Retrieves all available brands from the API.
     *
     * @return array|null An array of brands or null if no brands are available
     * @throws PSApiException If retrieval of brands fails
     */
    public function getAll(): ?array;

    /**
     * Retrieves all brands added after a specific date.
     *
     * @param \DateTimeInterface|string $fromDate The date from which to retrieve brands
     * @return array|null An array of brands or null if no brands match
     * @throws PSApiException If retrieval fails
     * @throws \InvalidArgumentException If the date format is invalid
     */
    public function getAllByDate(\DateTimeInterface|string $fromDate): ?array;

    /**
     * Retrieves all brands associated with the current user.
     *
     * @return array|null An array of user's brands or null if no brands are available
     * @throws PSApiException If retrieval of brands fails
     */
    public function getMyBrands(): ?array;

    /**
     * Creates a new brand or updates an existing brand.
     *
     * @param array $brandData The brand data array
     * @return int The brand ID (newly created or updated)
     * @throws PSApiException If the operation fails
     */
    public function createOrUpdateBrand(array $brandData): int;
}
