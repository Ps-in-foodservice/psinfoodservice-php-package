<?php

declare(strict_types=1);
namespace PSinfoodservice\Exceptions;

/**
 * Exception class for PS in foodservice API errors.
 */
class PSApiException extends \Exception
{
    /**
     * HTTP status code of the response.
     */
    private int $statusCode;

    /**
     * Trace ID from the API response for debugging purposes.
     */
    private ?string $traceId;

    /**
     * Initializes a new API exception.
     *
     * @param string $message The error message
     * @param int $statusCode The HTTP status code
     * @param string|null $traceId The trace ID from the API response, if available
     */
    public function __construct(string $message, int $statusCode, ?string $traceId = null)
    {
        $fullMessage = $traceId !== null ? "{$message} [TraceId: {$traceId}]" : $message;
        parent::__construct($fullMessage);
        $this->statusCode = $statusCode;
        $this->traceId = $traceId;
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

    /**
     * Gets the trace ID from the API response.
     *
     * The trace ID can be used to track requests in the API logs for debugging.
     *
     * @return string|null The trace ID or null if not available
     */
    public function getTraceId(): ?string
    {
        return $this->traceId;
    }
}
