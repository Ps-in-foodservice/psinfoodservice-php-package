<?php

namespace PSinfoodservice\Middleware;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PSinfoodservice\Exceptions\RateLimitException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Guzzle middleware for handling API rate limits
 *
 * The PS in Foodservice API enforces rate limits per endpoint.
 * This middleware can automatically handle rate limit errors by:
 * - Detecting 429 Too Many Requests responses
 * - Parsing the Retry-After header
 * - Optionally waiting and retrying automatically
 * - Throwing RateLimitException with retry information
 */
class RateLimitMiddleware
{
    /**
     * Whether to automatically wait and retry on rate limit
     *
     * @var bool
     */
    private bool $autoWait;

    /**
     * Maximum time to wait in seconds before giving up
     *
     * @var int
     */
    private int $maxWaitTime;

    /**
     * PSR-3 logger instance
     *
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Create a new rate limit middleware instance
     *
     * @param bool $autoWait Whether to automatically wait and retry (default: false)
     * @param int $maxWaitTime Maximum seconds to wait (default: 60)
     * @param LoggerInterface|null $logger Optional PSR-3 logger
     */
    public function __construct(
        bool $autoWait = false,
        int $maxWaitTime = 60,
        ?LoggerInterface $logger = null
    ) {
        $this->autoWait = $autoWait;
        $this->maxWaitTime = max(1, $maxWaitTime);
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * Create the middleware handler
     *
     * @return callable
     */
    public function __invoke(callable $handler): callable
    {
        return function (Request $request, array $options) use ($handler) {
            // Check if auto-wait is disabled for this request
            if (isset($options['rate_limit_auto_wait'])) {
                $autoWait = $options['rate_limit_auto_wait'];
            } else {
                $autoWait = $this->autoWait;
            }

            return $this->handleRequest($handler, $request, $options, $autoWait);
        };
    }

    /**
     * Handle the request with rate limit detection
     *
     * @param callable $handler The next handler in the chain
     * @param Request $request The request to execute
     * @param array $options Request options
     * @param bool $autoWait Whether to auto-wait on rate limit
     * @return PromiseInterface
     */
    private function handleRequest(
        callable $handler,
        Request $request,
        array $options,
        bool $autoWait
    ): PromiseInterface {
        return $handler($request, $options)->then(
            // Success handler - check for rate limit response
            function (Response $response) use ($handler, $request, $options, $autoWait) {
                if ($response->getStatusCode() === 429) {
                    return $this->handleRateLimit($handler, $request, $options, $response, $autoWait);
                }
                return $response;
            },
            // Error handler - check for rate limit exception
            function ($exception) use ($handler, $request, $options, $autoWait) {
                if ($exception instanceof ClientException && $exception->getResponse()->getStatusCode() === 429) {
                    return $this->handleRateLimit(
                        $handler,
                        $request,
                        $options,
                        $exception->getResponse(),
                        $autoWait
                    );
                }
                throw $exception;
            }
        );
    }

    /**
     * Handle a rate limit response
     *
     * @param callable $handler The handler to retry with
     * @param Request $request The original request
     * @param array $options Request options
     * @param Response $response The 429 response
     * @param bool $autoWait Whether to auto-wait and retry
     * @return PromiseInterface|Response
     * @throws RateLimitException
     */
    private function handleRateLimit(
        callable $handler,
        Request $request,
        array $options,
        Response $response,
        bool $autoWait
    ) {
        $retryAfter = $this->parseRetryAfter($response);
        $endpoint = (string)$request->getUri();

        $this->logger->warning('Rate limit exceeded', [
            'endpoint' => $endpoint,
            'retry_after' => $retryAfter,
            'auto_wait' => $autoWait
        ]);

        // If auto-wait is enabled and wait time is acceptable, wait and retry
        if ($autoWait && $retryAfter <= $this->maxWaitTime) {
            $this->logger->info('Automatically waiting for rate limit', [
                'wait_seconds' => $retryAfter,
                'endpoint' => $endpoint
            ]);

            sleep($retryAfter);

            $this->logger->info('Retrying after rate limit wait', [
                'endpoint' => $endpoint
            ]);

            // Retry the request
            return $handler($request, $options);
        }

        // Otherwise, throw exception with retry information
        throw new RateLimitException(
            'Rate limit exceeded',
            $retryAfter,
            $endpoint,
            $this->extractRateLimit($response),
            $this->extractTraceId($response)
        );
    }

    /**
     * Parse the Retry-After header from response
     *
     * @param Response $response
     * @return int Seconds to wait
     */
    private function parseRetryAfter(Response $response): int
    {
        $retryAfter = $response->getHeaderLine('Retry-After');

        if (empty($retryAfter)) {
            // Default to 1 second if header not present
            return 1;
        }

        // Retry-After can be either seconds or HTTP date
        if (is_numeric($retryAfter)) {
            return (int)$retryAfter;
        }

        // Try to parse as HTTP date
        $retryTime = strtotime($retryAfter);
        if ($retryTime !== false) {
            $waitSeconds = max(1, $retryTime - time());
            return $waitSeconds;
        }

        return 1;
    }

    /**
     * Extract rate limit from response headers or body
     *
     * @param Response $response
     * @return int|null Rate limit in requests/second
     */
    private function extractRateLimit(Response $response): ?int
    {
        // Check for X-RateLimit-Limit header
        $rateLimitHeader = $response->getHeaderLine('X-RateLimit-Limit');
        if (!empty($rateLimitHeader) && is_numeric($rateLimitHeader)) {
            return (int)$rateLimitHeader;
        }

        // Try to extract from response body
        try {
            $body = json_decode($response->getBody()->getContents(), true);
            if (isset($body['rateLimit'])) {
                return (int)$body['rateLimit'];
            }
        } catch (\Exception $e) {
            // Ignore parse errors
        }

        return null;
    }

    /**
     * Extract trace ID from response
     *
     * @param Response $response
     * @return string|null
     */
    private function extractTraceId(Response $response): ?string
    {
        try {
            $body = json_decode($response->getBody()->getContents(), true);
            return $body['traceId'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if auto-wait is enabled
     *
     * @return bool
     */
    public function isAutoWaitEnabled(): bool
    {
        return $this->autoWait;
    }

    /**
     * Get the maximum wait time
     *
     * @return int Maximum seconds to wait
     */
    public function getMaxWaitTime(): int
    {
        return $this->maxWaitTime;
    }
}
