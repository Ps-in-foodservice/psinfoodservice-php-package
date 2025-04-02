<?php
namespace PSinfoodservice\Domain;

class RequestUpdateArticle extends RequestUpdate
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