<?php

namespace PSinfoodservice\Dtos\Outgoing;

/**
 * Validation error details
 *
 * @XmlTitle(ElementName="ValidationError")
 */
class ValidationErrorDto
{
    /**
     * Position or field name where the validation error occurred
     */
    public ?string $Position = null;

    /**
     * Error message describing the validation failure
     */
    public ?string $ErrorMessage = null;
}
