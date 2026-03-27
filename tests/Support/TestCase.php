<?php

declare(strict_types=1);

namespace PSinfoodservice\Tests\Support;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase as BaseTestCase;
use PSinfoodservice\PSinfoodserviceClient;

/**
 * Base test case with common helpers for testing
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * Create a mock HTTP client with predefined responses
     *
     * @param array $responses Array of Response objects
     * @return Client
     */
    protected function createMockClient(array $responses): Client
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);

        return new Client(['handler' => $handlerStack]);
    }

    /**
     * Create a mock PSinfoodserviceClient with mock HTTP client
     *
     * @param array $mockResponses Array of Response objects to mock
     * @return PSinfoodserviceClient
     * @throws \ReflectionException
     */
    protected function createMockPSClient(array $mockResponses = []): PSinfoodserviceClient
    {
        $client = new PSinfoodserviceClient('preproduction', '/v7/json', false, false);

        if (!empty($mockResponses)) {
            $mockHttpClient = $this->createMockClient($mockResponses);

            // Use reflection to inject mock client
            $reflection = new \ReflectionClass($client);
            $property = $reflection->getProperty('httpClient');
            $property->setAccessible(true);
            $property->setValue($client, $mockHttpClient);
        }

        return $client;
    }

    /**
     * Create a successful JSON response
     *
     * @param array $data Response data
     * @param int $statusCode HTTP status code
     * @return Response
     */
    protected function createJsonResponse(array $data, int $statusCode = 200): Response
    {
        return new Response($statusCode, [
            'Content-Type' => 'application/json'
        ], json_encode($data));
    }

    /**
     * Create an error JSON response
     *
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     * @param string|null $traceId Trace ID for tracking
     * @return Response
     */
    protected function createErrorResponse(
        string $message,
        int $statusCode = 400,
        ?string $traceId = null
    ): Response {
        return new Response($statusCode, [
            'Content-Type' => 'application/json'
        ], json_encode([
            'detail' => $message,
            'traceId' => $traceId ?? 'test-trace-' . uniqid()
        ]));
    }

    /**
     * Create a mock Response with custom headers
     *
     * @param int $statusCode HTTP status code
     * @param array $headers Response headers
     * @param string|null $body Response body
     * @return Response
     */
    protected function createMockResponse(
        int $statusCode = 200,
        array $headers = [],
        ?string $body = null
    ): Response {
        return new Response($statusCode, $headers, $body);
    }
}
