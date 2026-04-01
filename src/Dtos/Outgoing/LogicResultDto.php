<?php

declare(strict_types=1);

namespace PSinfoodservice\Dtos\Outgoing;

/**
 * Result of logic test
 *
 * Contains validation status and either validation errors or logic rules that would be executed.
 */
class LogicResultDto
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
     * Array of logic rules that would be executed
     * Only populated if validation passes
     *
     * @var array|null
     */
    public ?array $LogicRules = null;

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
