<?php

namespace PSinfoodservice\Tests\Unit\Exceptions;

use PHPUnit\Framework\TestCase;
use PSinfoodservice\Exceptions\PSApiException;

class PSApiExceptionTest extends TestCase
{
    public function test_constructs_with_message_and_status_code()
    {
        $exception = new PSApiException('Test error', 400);

        $this->assertSame(400, $exception->getStatusCode());
        $this->assertStringContainsString('Test error', $exception->getMessage());
    }

    public function test_includes_trace_id_in_message()
    {
        $exception = new PSApiException('Test error', 500, 'trace-123');

        $this->assertStringContainsString('trace-123', $exception->getMessage());
        $this->assertStringContainsString('Test error', $exception->getMessage());
        $this->assertSame('Test error [TraceId: trace-123]', $exception->getMessage());
    }

    public function test_message_format_with_null_trace_id()
    {
        $exception = new PSApiException('Error message', 404, null);

        $this->assertSame('Error message', $exception->getMessage());
        $this->assertStringNotContainsString('TraceId', $exception->getMessage());
    }

    public function test_extends_base_exception()
    {
        $exception = new PSApiException('Test', 500);

        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function test_get_status_code_returns_correct_value()
    {
        $exception400 = new PSApiException('Bad request', 400);
        $exception500 = new PSApiException('Server error', 500);
        $exception404 = new PSApiException('Not found', 404);

        $this->assertSame(400, $exception400->getStatusCode());
        $this->assertSame(500, $exception500->getStatusCode());
        $this->assertSame(404, $exception404->getStatusCode());
    }

    public function test_message_with_empty_string_trace_id()
    {
        $exception = new PSApiException('Test error', 400, '');

        $this->assertSame('Test error [TraceId: ]', $exception->getMessage());
    }

    public function test_get_trace_id_returns_trace_id()
    {
        $exception = new PSApiException('Test error', 500, 'abc-123-def');

        $this->assertSame('abc-123-def', $exception->getTraceId());
    }

    public function test_get_trace_id_returns_null_when_not_set()
    {
        $exception = new PSApiException('Test error', 500);

        $this->assertNull($exception->getTraceId());
    }

    public function test_get_trace_id_returns_null_when_null_passed()
    {
        $exception = new PSApiException('Test error', 500, null);

        $this->assertNull($exception->getTraceId());
    }

    public function test_get_trace_id_returns_empty_string_when_empty_passed()
    {
        $exception = new PSApiException('Test error', 500, '');

        $this->assertSame('', $exception->getTraceId());
    }
}
