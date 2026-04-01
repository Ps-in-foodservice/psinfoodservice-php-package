<?php

namespace PSinfoodservice\Tests\Unit\Domain;

use PHPUnit\Framework\TestCase;
use PSinfoodservice\Domain\Environment;

class EnvironmentTest extends TestCase
{
    public function test_is_valid_returns_true_for_supported_environments()
    {
        $this->assertTrue(Environment::isValid('preproduction'));
        $this->assertTrue(Environment::isValid('production'));
        $this->assertTrue(Environment::isValid('test'));
        $this->assertTrue(Environment::isValid('development'));
    }

    public function test_is_valid_returns_false_for_unsupported_environments()
    {
        $this->assertFalse(Environment::isValid('staging'));
        $this->assertFalse(Environment::isValid('invalid'));
        $this->assertFalse(Environment::isValid(''));
        $this->assertFalse(Environment::isValid('PRODUCTION')); // Case sensitive
    }

    public function test_get_default_returns_preproduction()
    {
        $this->assertSame('preproduction', Environment::getDefault());
    }

    public function test_sanitize_returns_valid_environment_unchanged()
    {
        $this->assertSame('production', Environment::sanitize('production'));
        $this->assertSame('preproduction', Environment::sanitize('preproduction'));
        $this->assertSame('test', Environment::sanitize('test'));
        $this->assertSame('development', Environment::sanitize('development'));
    }

    public function test_sanitize_returns_default_for_invalid_environment()
    {
        $this->assertSame('preproduction', Environment::sanitize('invalid'));
        $this->assertSame('preproduction', Environment::sanitize('staging'));
        $this->assertSame('preproduction', Environment::sanitize(''));
        $this->assertSame('preproduction', Environment::sanitize('PRODUCTION'));
    }

    public function test_validate_returns_valid_environment()
    {
        $this->assertSame('production', Environment::validate('production'));
        $this->assertSame('preproduction', Environment::validate('preproduction'));
        $this->assertSame('test', Environment::validate('test'));
        $this->assertSame('development', Environment::validate('development'));
    }

    public function test_validate_throws_exception_for_invalid_environment()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid environment');

        Environment::validate('invalid');
    }

    public function test_validate_exception_includes_valid_environments()
    {
        try {
            Environment::validate('staging');
            $this->fail('Expected exception was not thrown');
        } catch (\InvalidArgumentException $e) {
            $message = $e->getMessage();
            $this->assertStringContainsString('preproduction', $message);
            $this->assertStringContainsString('production', $message);
            $this->assertStringContainsString('test', $message);
            $this->assertStringContainsString('development', $message);
        }
    }

    public function test_validate_throws_for_empty_string()
    {
        $this->expectException(\InvalidArgumentException::class);

        Environment::validate('');
    }

    public function test_validate_throws_for_case_mismatch()
    {
        $this->expectException(\InvalidArgumentException::class);

        Environment::validate('PRODUCTION'); // Should be 'production'
    }
}
