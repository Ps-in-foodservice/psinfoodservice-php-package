<?php

declare(strict_types=1);
namespace PSinfoodservice\Helpers;

use GuzzleHttp\Promise\Utils;
use GuzzleHttp\Promise\PromiseInterface;
use PSinfoodservice\PSinfoodserviceClient;
use PSinfoodservice\Exceptions\PSApiException;

/**
 * Helper class for executing multiple API requests concurrently.
 *
 * This class provides async/concurrent request functionality using Guzzle's
 * promise-based async capabilities. Use this when you need to make multiple
 * independent API calls and want to execute them in parallel for better performance.
 *
 * @example
 * ```php
 * $async = new AsyncClient($client);
 *
 * // Queue multiple requests
 * $async->addRequest('brands', 'GET', 'Brand/All');
 * $async->addRequest('masters', 'GET', 'Master/All');
 * $async->addRequest('scores', 'GET', 'ImpactScore/all');
 *
 * // Execute all requests concurrently
 * $results = $async->execute();
 *
 * // Access results by key
 * $brands = $results['brands'];
 * $masters = $results['masters'];
 * ```
 */
class AsyncClient
{
    /**
     * Queued requests to execute.
     *
     * @var array<string, array{method: string, path: string, options: array}>
     */
    private array $requests = [];

    /**
     * Maximum number of concurrent requests.
     */
    private int $concurrency = 10;

    /**
     * Initialize the async client.
     *
     * @param PSinfoodserviceClient $client The PS in foodservice client
     */
    public function __construct(
        private PSinfoodserviceClient $client
    ) {}

    /**
     * Set the maximum number of concurrent requests.
     *
     * @param int $concurrency Maximum concurrent requests (default: 10)
     * @return self
     */
    public function setConcurrency(int $concurrency): self
    {
        $this->concurrency = max(1, $concurrency);
        return $this;
    }

    /**
     * Add a request to the queue.
     *
     * @param string $key Unique identifier for this request (used to retrieve results)
     * @param string $method HTTP method (GET, POST, PUT, DELETE)
     * @param string $path API endpoint path (will be prefixed with API version)
     * @param array $options Guzzle request options (json, query, headers, etc.)
     * @return self
     */
    public function addRequest(string $key, string $method, string $path, array $options = []): self
    {
        $this->requests[$key] = [
            'method' => strtoupper($method),
            'path' => $path,
            'options' => $options
        ];
        return $this;
    }

    /**
     * Add a GET request to the queue.
     *
     * @param string $key Unique identifier for this request
     * @param string $path API endpoint path
     * @param array $query Query parameters
     * @return self
     */
    public function get(string $key, string $path, array $query = []): self
    {
        $options = !empty($query) ? ['query' => $query] : [];
        return $this->addRequest($key, 'GET', $path, $options);
    }

    /**
     * Add a POST request to the queue.
     *
     * @param string $key Unique identifier for this request
     * @param string $path API endpoint path
     * @param mixed $body Request body (will be JSON encoded)
     * @return self
     */
    public function post(string $key, string $path, mixed $body = null): self
    {
        $options = $body !== null ? ['json' => $body] : [];
        return $this->addRequest($key, 'POST', $path, $options);
    }

    /**
     * Clear all queued requests.
     *
     * @return self
     */
    public function clear(): self
    {
        $this->requests = [];
        return $this;
    }

    /**
     * Get the number of queued requests.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->requests);
    }

    /**
     * Execute all queued requests concurrently.
     *
     * Returns an array of results indexed by the request keys.
     * Failed requests will have their exception stored instead of the result.
     *
     * @param bool $throwOnError If true, throws the first encountered exception.
     *                           If false, exceptions are stored in the results array.
     * @return array<string, mixed> Results indexed by request key
     * @throws PSApiException If throwOnError is true and any request fails
     */
    public function execute(bool $throwOnError = false): array
    {
        if (empty($this->requests)) {
            return [];
        }

        $httpClient = $this->client->getHttpClient();
        $promises = [];

        foreach ($this->requests as $key => $request) {
            $fullPath = $this->client->buildApiPath($request['path']);
            $promises[$key] = $httpClient->requestAsync(
                $request['method'],
                $fullPath,
                $request['options']
            );
        }

        // Execute all promises concurrently
        $results = Utils::settle($promises)->wait();

        // Process results
        $output = [];
        foreach ($results as $key => $result) {
            if ($result['state'] === 'fulfilled') {
                $response = $result['value'];
                $body = $response->getBody()->getContents();
                $output[$key] = json_decode($body);
            } else {
                $exception = $result['reason'];

                if ($throwOnError) {
                    if ($exception instanceof \GuzzleHttp\Exception\ClientException) {
                        $errorResponse = json_decode($exception->getResponse()->getBody()->getContents(), true);
                        throw new PSApiException(
                            $errorResponse['detail'] ?? $errorResponse['title'] ?? 'Request failed',
                            $exception->getResponse()->getStatusCode(),
                            $errorResponse['traceId'] ?? null
                        );
                    }
                    throw new PSApiException($exception->getMessage(), 500);
                }

                $output[$key] = $exception;
            }
        }

        // Clear requests after execution
        $this->clear();

        return $output;
    }

    /**
     * Execute requests and return only successful results.
     *
     * Failed requests are silently ignored.
     *
     * @return array<string, mixed> Successful results indexed by request key
     */
    public function executeSuccessful(): array
    {
        $results = $this->execute(false);

        return array_filter($results, function ($result) {
            return !($result instanceof \Throwable);
        });
    }

    /**
     * Execute requests with a callback for each result.
     *
     * This is useful for processing results as they complete.
     *
     * @param callable $onSuccess Callback for successful results: function(string $key, mixed $data)
     * @param callable|null $onError Callback for failed requests: function(string $key, \Throwable $error)
     * @return void
     */
    public function executeWithCallbacks(callable $onSuccess, ?callable $onError = null): void
    {
        $results = $this->execute(false);

        foreach ($results as $key => $result) {
            if ($result instanceof \Throwable) {
                if ($onError !== null) {
                    $onError($key, $result);
                }
            } else {
                $onSuccess($key, $result);
            }
        }
    }
}
