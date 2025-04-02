<?php
namespace PSinfoodservice\Domain;

class RequestUpdate
{
    /**
     * Default to 1 day ago
     * @var string
     */
    public $LastUpdatedAfter;

    /**
     * Target market
     * @var int
     */
    public $TargetMarket = 0;

    public function __construct()
    {
        // Set default LastUpdatedAfter to yesterday
        $this->LastUpdatedAfter = date('c', strtotime('-1 day'));
    }
}