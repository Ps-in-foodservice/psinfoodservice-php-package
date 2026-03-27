<?php

declare(strict_types=1);

namespace PSinfoodservice\Exceptions;

/**
 * Exception thrown when API rate limit is exceeded
 *
 * The PS in Foodservice API has rate limits per endpoint to prevent abuse.
 * When you exceed the rate limit, a 429 Too Many Requests response is returned
 * along with a Retry-After header indicating when you can retry.
 *
 * @example
 * ```php
 * try {
 *     $products = $client->webApi->getMyProducts();
 * } catch (RateLimitException $e) {
 *     $waitSeconds = $e->getRetryAfter();
 *     echo "Rate limit exceeded. Retry after {$waitSeconds} seconds.\n";
 *     sleep($waitSeconds);
 *     // Retry the request
 *     $products = $client->webApi->getMyProducts();
 * }
 * ```
 */
class RateLimitException extends PSApiException
{
    /**
     * Number of seconds to wait before retrying
     *
     * @var int
     */
    private int $retryAfter;

    /**
     * The endpoint that hit the rate limit
     *
     * @var string|null
     */
    private ?string $endpoint;

    /**
     * The rate limit for this endpoint (requests per second)
     *
     * @var int|null
     */
    private ?int $rateLimit;

    /**
     * Create a new RateLimitException
     *
     * @param string $message Error message
     * @param int $retryAfter Seconds to wait before retrying
     * @param string|null $endpoint The endpoint that hit the rate limit
     * @param int|null $rateLimit The rate limit (requests/second)
     * @param string|null $traceId Optional trace ID for debugging
     */
    public function __construct(
        string $message,
        int $retryAfter = 1,
        ?string $endpoint = null,
        ?int $rateLimit = null,
        ?string $traceId = null
    ) {
        parent::__construct($message, 429, $traceId);
        $this->retryAfter = max(1, $retryAfter);
        $this->endpoint = $endpoint;
        $this->rateLimit = $rateLimit;
    }

    /**
     * Get the number of seconds to wait before retrying
     *
     * @return int Seconds to wait
     */
    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }

    /**
     * Get the endpoint that hit the rate limit
     *
     * @return string|null The endpoint URL or null if not available
     */
    public function getEndpoint(): ?string
    {
        return $this->endpoint;
    }

    /**
     * Get the rate limit for this endpoint
     *
     * @return int|null Rate limit in requests per second, or null if not available
     */
    public function getRateLimit(): ?int
    {
        return $this->rateLimit;
    }

    /**
     * Get a user-friendly error message
     *
     * @return string Formatted error message
     */
    public function getUserMessage(): string
    {
        $msg = "Rate limit exceeded.";

        if ($this->rateLimit !== null) {
            $msg .= " Limit: {$this->rateLimit} requests/second.";
        }

        if ($this->endpoint !== null) {
            $msg .= " Endpoint: {$this->endpoint}.";
        }

        $msg .= " Please retry after {$this->retryAfter} second(s).";

        return $msg;
    }
}
