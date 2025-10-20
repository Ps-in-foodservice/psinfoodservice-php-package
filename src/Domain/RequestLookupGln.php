<?php
namespace PSinfoodservice\Domain;

/**
 * Request class for looking up GLN numbers.
 */
class RequestLookupGln extends RequestLookup
{
    /**
     * GLN identifier
     * @var string
     */
    public $GLN;
}