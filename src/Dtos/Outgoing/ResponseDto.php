<?php

namespace PSinfoodservice\Dtos\Outgoing;

/**
 * Standard response DTO for API operations
 *
 * Contains the result of an API operation including success status,
 * optional logistic ID, and any validation errors.
 */
class ResponseDto
{
    /**
     * Response header with metadata
     *
     * @XmlElement(ElementName="header")
     */
    public ?HeaderDto $Header = null;

    /**
     * Indicates whether the operation succeeded
     *
     * @XmlElement(ElementName="issucceeded")
     */
    public bool $IsSucceeded = false;

    /**
     * The ID of the created or updated logistic item
     *
     * @XmlElement(ElementName="logisticid")
     */
    public int $LogisticId = 0;

    /**
     * Array of validation errors, if any
     *
     * @var ValidationErrorDto[]|null
     * @XmlElement(ElementName="errors")
     */
    public ?array $Error = null;

    /**
     * General error message
     *
     * @XmlElement(elementName="errormessage")
     */
    public ?string $ErrorMessage = null;

    /**
     * Trace ID for debugging
     */
    public ?string $TraceId = null;
}
