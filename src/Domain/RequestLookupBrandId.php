<?php
namespace PSinfoodservice\Domain;

/**
 * Request class for looking up Brandid.
 */
class RequestLookupBrandId extends RequestLookup
{
    /**
     * brandid
     * @var int
     */
    public $BrandId;
}