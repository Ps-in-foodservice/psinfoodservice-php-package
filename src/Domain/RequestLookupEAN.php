<?php
namespace PSinfoodservice\Domain;

/**
 * Request class for looking up EAN numbers.
 */
class RequestLookupEAN extends RequestLookup
{
    /**
     * List of search criteria
     * @var array
     */
    public $SearchCriteria = [];

    /**
     * Set the search criteria
     * @param array $criteria The array of Gtins
     * @return $this
     */
    public function setSearchCriteria(array $criteria)
    {
        $this->SearchCriteria = $criteria;
        return $this;
    }

    /**
     * Set the LastUpdatedAfter date
     * @param string $date Date in ISO 8601 format
     * @return $this
     */
    public function setLastUpdatedAfter($date)
    {
        $this->LastUpdatedAfter = $date;
        return $this;
    }

    /**
     * Set the TargetMarket
     * @param int $market Target market ID
     * @return $this
     */
    public function setTargetMarket($market)
    {
        $this->TargetMarket = $market;
        return $this;
    }
}