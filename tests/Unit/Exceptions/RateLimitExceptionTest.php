<?php

namespace PSinfoodservice\Tests\Unit\Exceptions;

use PHPUnit\Framework\TestCase;
use PSinfoodservice\Exceptions\RateLimitException;
use PSinfoodservice\Exceptions\PSApiException;

class RateLimitExceptionTest extends TestCase
{
    public function test_constructs_with_basic_parameters()
    {
        $exception = new RateLimitException('Rate limit exceeded', 5);

        $this->assertSame(429, $exception->getStatusCode());
        $this->assertSame(5, $exception->getRetryAfter());
        $this->assertStringContainsString('Rate limit exceeded', $exception->getMessage());
    }

    public function test_retry_after_has_minimum_of_one()
    {
        $exception = new RateLimitException('Rate limit', 0);
        $this->assertSame(1, $exception->getRetryAfter());

        $exception2 = new RateLimitException('Rate limit', -5);
        $this->assertSame(1, $exception2->getRetryAfter());

        $exception3 = new RateLimitException('Rate limit', -100);
        $this->assertSame(1, $exception3->getRetryAfter());
    }

    public function test_default_retry_after_is_one()
    {
        $exception = new RateLimitException('Rate limit');

        $this->assertSame(1, $exception->getRetryAfter());
    }

    public function test_stores_endpoint_and_rate_limit()
    {
        $exception = new RateLimitException(
            'Rate limit',
            10,
            '/api/products',
            50
        );

        $this->assertSame('/api/products', $exception->getEndpoint());
        $this->assertSame(50, $exception->getRateLimit());
    }

    public function test_endpoint_and_rate_limit_are_nullable()
    {
        $exception = new RateLimitException('Rate limit', 5);

        $this->assertNull($exception->getEndpoint());
        $this->assertNull($exception->getRateLimit());
    }

    public function test_get_user_message_with_all_info()
    {
        $exception = new RateLimitException(
            'Rate limit',
            30,
            '/api/lookup',
            10
        );

        $message = $exception->getUserMessage();

        $this->assertStringContainsString('Rate limit exceeded', $message);
        $this->assertStringContainsString('10 requests/second', $message);
        $this->assertStringContainsString('/api/lookup', $message);
        $this->assertStringContainsString('30 second(s)', $message);
    }

    public function test_get_user_message_without_optional_fields()
    {
        $exception = new RateLimitException('Rate limit', 5);

        $message = $exception->getUserMessage();

        $this->assertStringContainsString('Rate limit exceeded', $message);
        $this->assertStringContainsString('5 second(s)', $message);
        $this->assertStringNotContainsString('requests/second', $message);
        $this->assertStringNotContainsString('Endpoint:', $message);
    }

    public function test_get_user_message_with_only_rate_limit()
    {
        $exception = new RateLimitException('Rate limit', 10, null, 25);

        $message = $exception->getUserMessage();

        $this->assertStringContainsString('25 requests/second', $message);
        $this->assertStringNotContainsString('Endpoint:', $message);
    }

    public function test_get_user_message_with_only_endpoint()
    {
        $exception = new RateLimitException('Rate limit', 15, '/v7/json/Account/Login');

        $message = $exception->getUserMessage();

        $this->assertStringContainsString('/v7/json/Account/Login', $message);
        $this->assertStringNotContainsString('requests/second', $message);
    }

    public function test_extends_ps_api_exception()
    {
        $exception = new RateLimitException('Test', 10);

        $this->assertInstanceOf(PSApiException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function test_includes_trace_id_in_message()
    {
        $exception = new RateLimitException(
            'Rate limit',
            5,
            null,
            null,
            'trace-abc-123'
        );

        $this->assertStringContainsString('trace-abc-123', $exception->getMessage());
    }

    public function test_status_code_is_always_429()
    {
        $exception1 = new RateLimitException('Test 1', 1);
        $exception2 = new RateLimitException('Test 2', 100, '/endpoint', 50);
        $exception3 = new RateLimitException('Test 3', 0);

        $this->assertSame(429, $exception1->getStatusCode());
        $this->assertSame(429, $exception2->getStatusCode());
        $this->assertSame(429, $exception3->getStatusCode());
    }
}
