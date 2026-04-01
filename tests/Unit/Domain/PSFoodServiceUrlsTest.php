<?php

namespace PSinfoodservice\Tests\Unit\Domain;

use PHPUnit\Framework\TestCase;
use PSinfoodservice\Domain\PSFoodServiceUrls;
use PSinfoodservice\Domain\Environment;

class PSFoodServiceUrlsTest extends TestCase
{
    private PSFoodServiceUrls $urls;

    protected function setUp(): void
    {
        $this->urls = new PSFoodServiceUrls();
    }

    public function test_get_base_url_returns_production_url()
    {
        $url = $this->urls->getBaseUrl(Environment::production);

        $this->assertSame('https://webapi.psinfoodservice.com', $url);
    }

    public function test_get_base_url_returns_preproduction_url()
    {
        $url = $this->urls->getBaseUrl(Environment::staging);

        $this->assertSame('https://staging-api.psinfoodservice.com', $url);
    }

    public function test_get_base_url_returns_test_url()
    {
        $url = $this->urls->getBaseUrl(Environment::test);

        $this->assertSame('https://test-api.psinfoodservice.com', $url);
    }

    public function test_get_base_url_returns_development_url()
    {
        $url = $this->urls->getBaseUrl(Environment::development);

        $this->assertSame('https://localhost:5001', $url);
    }

    public function test_get_base_url_throws_exception_for_invalid_environment()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid environment');

        $this->urls->getBaseUrl('invalid');
    }

    public function test_production_url_has_https()
    {
        $production = $this->urls->getBaseUrl(Environment::production);

        $this->assertStringStartsWith('https://', $production);
    }

    public function test_test_url_has_https()
    {
        $test = $this->urls->getBaseUrl(Environment::test);

        $this->assertStringStartsWith('https://', $test);
    }

    public function test_development_url_has_https()
    {
        $development = $this->urls->getBaseUrl(Environment::development);

        $this->assertStringStartsWith('https://', $development);
    }

    public function test_urls_do_not_have_trailing_slash()
    {
        $production = $this->urls->getBaseUrl(Environment::production);
        $test = $this->urls->getBaseUrl(Environment::test);
        $development = $this->urls->getBaseUrl(Environment::development);

        $this->assertStringEndsNotWith('/', $production);
        $this->assertStringEndsNotWith('/', $test);
        $this->assertStringEndsNotWith('/', $development);
    }
}
