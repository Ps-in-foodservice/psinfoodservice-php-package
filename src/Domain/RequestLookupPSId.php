<?php

declare(strict_types=1);
namespace PSinfoodservice\Domain;

/**
 * Request class for looking up PS IDs.
 */
class RequestLookupPSId extends RequestLookup
{
    /**
     * List of search criteria
     * @var array
     */
    public $SearchCriteria = [];
}
