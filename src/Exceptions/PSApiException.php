<?php

/**
 * Exception class for PS in foodservice API errors.
 */
namespace PSinfoodservice\Exceptions;

class PSApiException extends \Exception
{
    /**
     * HTTP status code of the response.
     */
    private int $statusCode;

    /**
     * Initializes a new API exception.
     *
     * @param string $message The error message
     * @param int $statusCode The HTTP status code
     * @param string|null $traceId The trace ID from the API response, if available
     */
    public function __construct(string $message, int $statusCode, ?string $traceId = null)
    {
        parent::__construct($message . ' - [' . $traceId . ']');
        $this->statusCode = $statusCode;
    }

    /**
     * Gets the HTTP status code associated with this exception.
     *
     * @return int The HTTP status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}