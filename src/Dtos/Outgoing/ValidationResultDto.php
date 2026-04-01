<?php

declare(strict_types=1);

namespace PSinfoodservice\Dtos\Outgoing;

/**
 * Result of validation test
 *
 * Contains validation status and any validation errors found during testing.
 */
class ValidationResultDto
{
    /**
     * Response header with metadata
     *
     * @var HeaderDto|null
     */
    public ?HeaderDto $Header = null;

    /**
     * Indicates whether validation passed
     *
     * @var bool
     */
    public bool $IsValid = false;

    /**
     * Array of validation errors, if any
     *
     * @var ValidationErrorDto[]|null
     */
    public ?array $Errors = null;

    /**
     * Trace ID for debugging
     *
     * @var string|null
     */
    public ?string $TraceId = null;

    /**
     * General error message
     *
     * @var string|null
     */
    public ?string $ErrorMessage = null;
}
