<?php

namespace PSinfoodservice\Tests\Unit\Domain;

use PHPUnit\Framework\TestCase;
use PSinfoodservice\Domain\Language;

class LanguageTest extends TestCase
{
    public function test_is_valid_returns_true_for_supported_languages()
    {
        $this->assertTrue(Language::isValid('nl'));
        $this->assertTrue(Language::isValid('fr'));
        $this->assertTrue(Language::isValid('en'));
        $this->assertTrue(Language::isValid('de'));
        $this->assertTrue(Language::isValid('all'));
    }

    public function test_is_valid_returns_false_for_unsupported_languages()
    {
        $this->assertFalse(Language::isValid('es'));
        $this->assertFalse(Language::isValid('it'));
        $this->assertFalse(Language::isValid('invalid'));
        $this->assertFalse(Language::isValid(''));
        $this->assertFalse(Language::isValid('NL')); // Case sensitive
    }

    public function test_get_default_returns_dutch()
    {
        $this->assertSame('nl', Language::getDefault());
    }

    public function test_sanitize_returns_valid_language_unchanged()
    {
        $this->assertSame('en', Language::sanitize('en'));
        $this->assertSame('fr', Language::sanitize('fr'));
        $this->assertSame('de', Language::sanitize('de'));
        $this->assertSame('nl', Language::sanitize('nl'));
        $this->assertSame('all', Language::sanitize('all'));
    }

    public function test_sanitize_returns_default_for_invalid_language()
    {
        $this->assertSame('nl', Language::sanitize('invalid'));
        $this->assertSame('nl', Language::sanitize('es'));
        $this->assertSame('nl', Language::sanitize(''));
        $this->assertSame('nl', Language::sanitize('NL')); // Case sensitive
    }

    public function test_validate_returns_valid_language()
    {
        $this->assertSame('en', Language::validate('en'));
        $this->assertSame('de', Language::validate('de'));
        $this->assertSame('fr', Language::validate('fr'));
        $this->assertSame('nl', Language::validate('nl'));
        $this->assertSame('all', Language::validate('all'));
    }

    public function test_validate_throws_exception_for_invalid_language()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid language code');

        Language::validate('invalid');
    }

    public function test_validate_exception_includes_valid_codes()
    {
        try {
            Language::validate('xx');
            $this->fail('Expected exception was not thrown');
        } catch (\InvalidArgumentException $e) {
            $message = $e->getMessage();
            $this->assertStringContainsString('nl', $message);
            $this->assertStringContainsString('fr', $message);
            $this->assertStringContainsString('en', $message);
            $this->assertStringContainsString('de', $message);
            $this->assertStringContainsString('all', $message);
        }
    }

    public function test_validate_throws_for_empty_string()
    {
        $this->expectException(\InvalidArgumentException::class);

        Language::validate('');
    }

    public function test_validate_throws_for_case_mismatch()
    {
        $this->expectException(\InvalidArgumentException::class);

        Language::validate('EN'); // Should be 'en'
    }
}
