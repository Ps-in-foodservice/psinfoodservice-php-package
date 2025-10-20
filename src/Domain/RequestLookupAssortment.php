<?php
namespace PSinfoodservice\Domain;

/**
 * Request class for looking up assortment information.
 */
class RequestLookupAssortment extends RequestLookup
{
    /**
     * Assortment ID
     * @var string
     */
    public $AssortmentId;
}