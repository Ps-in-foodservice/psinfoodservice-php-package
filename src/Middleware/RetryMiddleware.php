<?php

namespace PSinfoodservice\Middleware;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Guzzle middleware for retrying failed requests
 *
 * Automatically retries requests that fail due to temporary errors:
 * - 5xx server errors (500, 502, 503, 504)
 * - Connection timeouts
 * - Network errors
 *
 * Uses exponential backoff strategy to avoid overwhelming the server.
 */
class RetryMiddleware
{
    /**
     * Maximum number of retry attempts
     *
     * @var int
     */
    private int $maxRetries;

    /**
     * Base delay in milliseconds for exponential backoff
     *
     * @var int
     */
    private int $baseDelay;

    /**
     * HTTP status codes that should trigger a retry
     *
     * @var int[]
     */
    private array $retryStatusCodes;

    /**
     * PSR-3 logger instance for logging retry attempts
     *
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Whether to use exponential backoff
     *
     * @var bool
     */
    private bool $useExponentialBackoff;

    /**
     * Create a new retry middleware instance
     *
     * @param int $maxRetries Maximum number of retry attempts (default: 3)
     * @param int $baseDelay Base delay in milliseconds (default: 1000)
     * @param int[] $retryStatusCodes HTTP status codes to retry (default: [500, 502, 503, 504])
     * @param LoggerInterface|null $logger Optional PSR-3 logger
     * @param bool $useExponentialBackoff Whether to use exponential backoff (default: true)
     */
    public function __construct(
        int $maxRetries = 3,
        int $baseDelay = 1000,
        array $retryStatusCodes = [500, 502, 503, 504],
        ?LoggerInterface $logger = null,
        bool $useExponentialBackoff = true
    ) {
        $this->maxRetries = max(0, $maxRetries);
        $this->baseDelay = max(0, $baseDelay);
        $this->retryStatusCodes = $retryStatusCodes;
        $this->logger = $logger ?? new NullLogger();
        $this->useExponentialBackoff = $useExponentialBackoff;
    }

    /**
     * Create the middleware handler
     *
     * @return callable
     */
    public function __invoke(callable $handler): callable
    {
        return function (Request $request, array $options) use ($handler) {
            // Check if retry is disabled for this specific request
            if (isset($options['retry_enabled']) && $options['retry_enabled'] === false) {
                return $handler($request, $options);
            }

            // Override max retries if specified in request options
            $maxRetries = $options['max_retries'] ?? $this->maxRetries;

            return $this->doRetry($handler, $request, $options, 0, $maxRetries);
        };
    }

    /**
     * Execute the request with retry logic
     *
     * @param callable $handler The next handler in the chain
     * @param Request $request The request to execute
     * @param array $options Request options
     * @param int $retryCount Current retry attempt number
     * @param int $maxRetries Maximum retry attempts for this request
     * @return PromiseInterface
     */
    private function doRetry(
        callable $handler,
        Request $request,
        array $options,
        int $retryCount,
        int $maxRetries
    ): PromiseInterface {
        return $handler($request, $options)->then(
            // Success handler
            function (Response $response) use ($handler, $request, $options, $retryCount, $maxRetries) {
                // Check if response status code should trigger a retry
                if ($retryCount < $maxRetries && $this->shouldRetryResponse($response)) {
                    $this->logRetry($request, $retryCount, $response->getStatusCode());
                    $this->sleep($retryCount);
                    return $this->doRetry($handler, $request, $options, $retryCount + 1, $maxRetries);
                }

                return $response;
            },
            // Error handler
            function ($reason) use ($handler, $request, $options, $retryCount, $maxRetries) {
                // Check if the error should trigger a retry
                if ($retryCount < $maxRetries && $this->shouldRetryException($reason)) {
                    $this->logRetryException($request, $retryCount, $reason);
                    $this->sleep($retryCount);
                    return $this->doRetry($handler, $request, $options, $retryCount + 1, $maxRetries);
                }

                // Re-throw the exception if we're not retrying
                throw $reason;
            }
        );
    }

    /**
     * Determine if a response should trigger a retry
     *
     * @param Response $response
     * @return bool
     */
    private function shouldRetryResponse(Response $response): bool
    {
        return in_array($response->getStatusCode(), $this->retryStatusCodes, true);
    }

    /**
     * Determine if an exception should trigger a retry
     *
     * @param mixed $exception
     * @return bool
     */
    private function shouldRetryException($exception): bool
    {
        // Retry on connection exceptions (timeouts, network errors)
        if ($exception instanceof ConnectException) {
            return true;
        }

        // Retry on request exceptions with retryable status codes
        if ($exception instanceof RequestException) {
            $response = $exception->getResponse();
            if ($response !== null) {
                return $this->shouldRetryResponse($response);
            }
        }

        return false;
    }

    /**
     * Sleep for the appropriate duration based on retry count
     *
     * @param int $retryCount Current retry attempt number
     * @return void
     */
    private function sleep(int $retryCount): void
    {
        if ($this->baseDelay <= 0) {
            return;
        }

        $delay = $this->calculateDelay($retryCount);

        // Convert milliseconds to microseconds
        usleep($delay * 1000);
    }

    /**
     * Calculate delay for the current retry attempt
     *
     * @param int $retryCount Current retry attempt number
     * @return int Delay in milliseconds
     */
    private function calculateDelay(int $retryCount): int
    {
        if (!$this->useExponentialBackoff) {
            return $this->baseDelay;
        }

        // Exponential backoff: baseDelay * (2 ^ retryCount)
        // First retry: 1s, second: 2s, third: 4s (if baseDelay = 1000)
        return $this->baseDelay * (2 ** $retryCount);
    }

    /**
     * Log a retry attempt for a response
     *
     * @param Request $request
     * @param int $retryCount
     * @param int $statusCode
     * @return void
     */
    private function logRetry(Request $request, int $retryCount, int $statusCode): void
    {
        $this->logger->warning('Retrying request due to error response', [
            'retry_count' => $retryCount + 1,
            'max_retries' => $this->maxRetries,
            'status_code' => $statusCode,
            'method' => $request->getMethod(),
            'uri' => (string)$request->getUri(),
            'delay_ms' => $this->calculateDelay($retryCount)
        ]);
    }

    /**
     * Log a retry attempt for an exception
     *
     * @param Request $request
     * @param int $retryCount
     * @param mixed $exception
     * @return void
     */
    private function logRetryException(Request $request, int $retryCount, $exception): void
    {
        $context = [
            'retry_count' => $retryCount + 1,
            'max_retries' => $this->maxRetries,
            'method' => $request->getMethod(),
            'uri' => (string)$request->getUri(),
            'delay_ms' => $this->calculateDelay($retryCount)
        ];

        if ($exception instanceof \Exception) {
            $context['exception'] = get_class($exception);
            $context['message'] = $exception->getMessage();
        }

        $this->logger->warning('Retrying request due to exception', $context);
    }

    /**
     * Get the maximum number of retries
     *
     * @return int
     */
    public function getMaxRetries(): int
    {
        return $this->maxRetries;
    }

    /**
     * Get the base delay in milliseconds
     *
     * @return int
     */
    public function getBaseDelay(): int
    {
        return $this->baseDelay;
    }

    /**
     * Get the retry status codes
     *
     * @return int[]
     */
    public function getRetryStatusCodes(): array
    {
        return $this->retryStatusCodes;
    }

    /**
     * Check if exponential backoff is enabled
     *
     * @return bool
     */
    public function isExponentialBackoffEnabled(): bool
    {
        return $this->useExponentialBackoff;
    }
}
