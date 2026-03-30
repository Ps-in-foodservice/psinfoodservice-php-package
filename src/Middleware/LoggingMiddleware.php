<?php

declare(strict_types=1);
namespace PSinfoodservice\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * Guzzle middleware for logging HTTP requests and responses.
 *
 * This middleware logs all API requests and responses using a PSR-3 logger.
 * Useful for debugging, auditing, and monitoring API usage.
 *
 * Features:
 * - Configurable log levels for requests, responses, and errors
 * - Option to log request/response bodies (disabled by default for security)
 * - Automatic redaction of sensitive headers (Authorization, etc.)
 * - Request timing information
 *
 * @example
 * ```php
 * use Monolog\Logger;
 * use Monolog\Handler\StreamHandler;
 *
 * $logger = new Logger('api');
 * $logger->pushHandler(new StreamHandler('api.log', Logger::DEBUG));
 *
 * $middleware = new LoggingMiddleware($logger, [
 *     'log_bodies' => true,
 *     'redact_headers' => ['Authorization', 'X-Api-Key']
 * ]);
 *
 * // Add to Guzzle handler stack
 * $stack->push($middleware);
 * ```
 */
class LoggingMiddleware
{
    /**
     * Headers that should be redacted in logs.
     *
     * @var array<string>
     */
    private array $redactHeaders = [
        'Authorization',
        'X-Api-Key',
        'Cookie',
        'Set-Cookie',
    ];

    /**
     * Whether to log request/response bodies.
     */
    private bool $logBodies = false;

    /**
     * Maximum body length to log (in bytes).
     */
    private int $maxBodyLength = 1000;

    /**
     * Log level for requests.
     */
    private string $requestLogLevel = LogLevel::DEBUG;

    /**
     * Log level for successful responses.
     */
    private string $responseLogLevel = LogLevel::DEBUG;

    /**
     * Log level for error responses (4xx, 5xx).
     */
    private string $errorLogLevel = LogLevel::WARNING;

    /**
     * Initialize the logging middleware.
     *
     * @param LoggerInterface $logger PSR-3 logger instance
     * @param array $options Configuration options:
     *   - log_bodies (bool): Log request/response bodies (default: false)
     *   - max_body_length (int): Max body length to log (default: 1000)
     *   - redact_headers (array): Headers to redact (merged with defaults)
     *   - request_level (string): Log level for requests (default: debug)
     *   - response_level (string): Log level for responses (default: debug)
     *   - error_level (string): Log level for errors (default: warning)
     */
    public function __construct(
        private LoggerInterface $logger,
        array $options = []
    ) {
        if (isset($options['log_bodies'])) {
            $this->logBodies = (bool) $options['log_bodies'];
        }

        if (isset($options['max_body_length'])) {
            $this->maxBodyLength = (int) $options['max_body_length'];
        }

        if (isset($options['redact_headers'])) {
            $this->redactHeaders = array_merge(
                $this->redactHeaders,
                (array) $options['redact_headers']
            );
        }

        if (isset($options['request_level'])) {
            $this->requestLogLevel = $options['request_level'];
        }

        if (isset($options['response_level'])) {
            $this->responseLogLevel = $options['response_level'];
        }

        if (isset($options['error_level'])) {
            $this->errorLogLevel = $options['error_level'];
        }
    }

    /**
     * Invoke the middleware.
     *
     * @param callable $handler The next handler in the stack
     * @return callable
     */
    public function __invoke(callable $handler): callable
    {
        return function (RequestInterface $request, array $options) use ($handler): PromiseInterface {
            $startTime = microtime(true);

            // Log the request
            $this->logRequest($request);

            return $handler($request, $options)->then(
                function (ResponseInterface $response) use ($request, $startTime) {
                    $duration = microtime(true) - $startTime;
                    $this->logResponse($request, $response, $duration);
                    return $response;
                },
                function (\Throwable $exception) use ($request, $startTime) {
                    $duration = microtime(true) - $startTime;
                    $this->logError($request, $exception, $duration);
                    throw $exception;
                }
            );
        };
    }

    /**
     * Log an outgoing request.
     *
     * @param RequestInterface $request
     */
    private function logRequest(RequestInterface $request): void
    {
        $context = [
            'method' => $request->getMethod(),
            'uri' => (string) $request->getUri(),
            'headers' => $this->redactSensitiveHeaders($request->getHeaders()),
        ];

        if ($this->logBodies) {
            $body = (string) $request->getBody();
            $request->getBody()->rewind();

            if (strlen($body) > $this->maxBodyLength) {
                $body = substr($body, 0, $this->maxBodyLength) . '... (truncated)';
            }

            $context['body'] = $body;
        }

        $this->logger->log(
            $this->requestLogLevel,
            sprintf('API Request: %s %s', $request->getMethod(), $request->getUri()->getPath()),
            $context
        );
    }

    /**
     * Log a response.
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param float $duration Request duration in seconds
     */
    private function logResponse(RequestInterface $request, ResponseInterface $response, float $duration): void
    {
        $statusCode = $response->getStatusCode();
        $isError = $statusCode >= 400;

        $context = [
            'method' => $request->getMethod(),
            'uri' => (string) $request->getUri(),
            'status_code' => $statusCode,
            'reason' => $response->getReasonPhrase(),
            'duration_ms' => round($duration * 1000, 2),
        ];

        if ($this->logBodies) {
            $body = (string) $response->getBody();
            $response->getBody()->rewind();

            if (strlen($body) > $this->maxBodyLength) {
                $body = substr($body, 0, $this->maxBodyLength) . '... (truncated)';
            }

            $context['body'] = $body;
        }

        $level = $isError ? $this->errorLogLevel : $this->responseLogLevel;

        $this->logger->log(
            $level,
            sprintf(
                'API Response: %s %s - %d %s (%.2fms)',
                $request->getMethod(),
                $request->getUri()->getPath(),
                $statusCode,
                $response->getReasonPhrase(),
                $duration * 1000
            ),
            $context
        );
    }

    /**
     * Log an error/exception.
     *
     * @param RequestInterface $request
     * @param \Throwable $exception
     * @param float $duration Request duration in seconds
     */
    private function logError(RequestInterface $request, \Throwable $exception, float $duration): void
    {
        $context = [
            'method' => $request->getMethod(),
            'uri' => (string) $request->getUri(),
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'duration_ms' => round($duration * 1000, 2),
        ];

        if ($exception instanceof \GuzzleHttp\Exception\RequestException && $exception->hasResponse()) {
            $response = $exception->getResponse();
            $context['status_code'] = $response->getStatusCode();
            $context['reason'] = $response->getReasonPhrase();

            if ($this->logBodies) {
                $body = (string) $response->getBody();
                if (strlen($body) > $this->maxBodyLength) {
                    $body = substr($body, 0, $this->maxBodyLength) . '... (truncated)';
                }
                $context['response_body'] = $body;
            }
        }

        $this->logger->log(
            $this->errorLogLevel,
            sprintf(
                'API Error: %s %s - %s',
                $request->getMethod(),
                $request->getUri()->getPath(),
                $exception->getMessage()
            ),
            $context
        );
    }

    /**
     * Redact sensitive headers from the log output.
     *
     * @param array<string, array<string>> $headers
     * @return array<string, array<string>>
     */
    private function redactSensitiveHeaders(array $headers): array
    {
        $redacted = [];

        foreach ($headers as $name => $values) {
            $isRedacted = false;
            foreach ($this->redactHeaders as $redactHeader) {
                if (strcasecmp($name, $redactHeader) === 0) {
                    $isRedacted = true;
                    break;
                }
            }

            $redacted[$name] = $isRedacted ? ['[REDACTED]'] : $values;
        }

        return $redacted;
    }
}
