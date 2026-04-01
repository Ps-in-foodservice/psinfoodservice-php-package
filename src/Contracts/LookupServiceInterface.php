<?php

declare(strict_types=1);
namespace PSinfoodservice\Contracts;

use PSinfoodservice\Domain\RequestLookupGtin;
use PSinfoodservice\Domain\RequestLookupPSId;
use PSinfoodservice\Domain\RequestLookupArticlenumber;
use PSinfoodservice\Domain\RequestLookupGln;
use PSinfoodservice\Domain\RequestLookupAssortment;
use PSinfoodservice\Domain\RequestLookupBrandId;
use PSinfoodservice\Domain\RequestLookup;
use PSinfoodservice\Dtos\Outgoing\LookupResultDto;
use PSinfoodservice\Exceptions\PSApiException;

/**
 * Interface for product lookup service operations.
 */
interface LookupServiceInterface
{
    /**
     * Looks up product information using GTIN numbers.
     *
     * @param RequestLookupGtin $request The lookup request containing GTIN data
     * @param bool $minimal Use minimal endpoint that returns only IDs
     * @return LookupResultDto|null The lookup response data or null if no data is available
     * @throws PSApiException If the lookup operation fails
     */
    public function Gtin(RequestLookupGtin $request, bool $minimal = false): ?LookupResultDto;

    /**
     * Looks up product information using PS IDs.
     *
     * @param RequestLookupPSId $request The lookup request containing PS ID data
     * @param bool $minimal Use minimal endpoint that returns only IDs
     * @return LookupResultDto|null The lookup response data or null if no data is available
     * @throws PSApiException If the lookup operation fails
     */
    public function PsId(RequestLookupPSId $request, bool $minimal = false): ?LookupResultDto;

    /**
     * Looks up product information using article numbers.
     *
     * @param RequestLookupArticlenumber $request The lookup request containing article number data
     * @param bool $minimal Use minimal endpoint that returns only IDs
     * @return LookupResultDto|null The lookup response data or null if no data is available
     * @throws PSApiException If the lookup operation fails
     */
    public function ArticleNumber(RequestLookupArticlenumber $request, bool $minimal = false): ?LookupResultDto;

    /**
     * Looks up product information using GLN (Global Location Number).
     *
     * @param RequestLookupGln $request The lookup request containing GLN data
     * @param bool $minimal Use minimal endpoint that returns only IDs
     * @return LookupResultDto|null The lookup response data or null if no data is available
     * @throws PSApiException If the lookup operation fails
     */
    public function GLN(RequestLookupGln $request, bool $minimal = false): ?LookupResultDto;

    /**
     * Looks up product assortment information.
     *
     * @param RequestLookupAssortment $request The lookup request containing assortment data
     * @param bool $minimal Use minimal endpoint that returns only IDs
     * @return LookupResultDto|null The lookup response data or null if no data is available
     * @throws PSApiException If the lookup operation fails
     */
    public function Assortment(RequestLookupAssortment $request, bool $minimal = false): ?LookupResultDto;

    /**
     * Looks up product by brand ID.
     *
     * @param RequestLookupBrandId $request The lookup request containing brand ID
     * @param bool $minimal Use minimal endpoint that returns only IDs
     * @return LookupResultDto|null The lookup response data or null if no data is available
     * @throws PSApiException If the lookup operation fails
     */
    public function BrandId(RequestLookupBrandId $request, bool $minimal = false): ?LookupResultDto;

    /**
     * Looks up all products by change date.
     *
     * @param RequestLookup $request The lookup request containing change date
     * @param bool $minimal Use minimal endpoint that returns only IDs
     * @return LookupResultDto|null The lookup response data or null if no data is available
     * @throws PSApiException If the lookup operation fails
     */
    public function All(RequestLookup $request, bool $minimal = false): ?LookupResultDto;
}
