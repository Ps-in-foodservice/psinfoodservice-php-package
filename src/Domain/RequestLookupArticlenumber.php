<?php
namespace PSinfoodservice\Domain;

/**
 * Request class for looking up article numbers.
 */
class RequestLookupArticlenumber extends RequestLookup
{
	/**
	 * List of search criteria
	 * @var array
	 */
	public $SearchCriteria = [];

	/**
	 * GLN identifier
	 * @var string
	 */
	public $GLN;

	/**
	 * Relation ID
	 * @var int
	 */
	public $RelationId;
}


