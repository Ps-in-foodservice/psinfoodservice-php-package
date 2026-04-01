<?php

namespace PSinfoodservice\Tests\Unit\Services;

use GuzzleHttp\Psr7\Response;
use PSinfoodservice\Exceptions\PSApiException;
use PSinfoodservice\Tests\Support\TestCase;

class MijnPSServiceTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = sys_get_temp_dir() . '/mijnps_test_' . uniqid();
        mkdir($this->tempDir, 0777, true);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if (is_dir($this->tempDir)) {
            array_map('unlink', glob($this->tempDir . '/*'));
            rmdir($this->tempDir);
        }
    }

    public function test_upload_assortment_returns_true_on_success()
    {
        $testFile = $this->tempDir . '/test.csv';
        file_put_contents($testFile, 'GTIN,ArticleNumber\n1234567890123,ABC-001');

        $client = $this->createMockPSClient([
            new Response(200)
        ]);

        $result = $client->mijnPS->uploadAssortment(
            '00000000-0000-0000-0000-000000000000',
            $testFile
        );

        $this->assertTrue($result);
    }

    public function test_upload_assortment_with_custom_filename()
    {
        $testFile = $this->tempDir . '/test.csv';
        file_put_contents($testFile, 'GTIN,ArticleNumber\n1234567890123,ABC-001');

        $client = $this->createMockPSClient([
            new Response(200)
        ]);

        $result = $client->mijnPS->uploadAssortment(
            '12345678-1234-1234-1234-123456789012',
            $testFile,
            'custom_filename.csv'
        );

        $this->assertTrue($result);
    }

    public function test_upload_assortment_throws_exception_for_missing_file()
    {
        $client = $this->createMockPSClient([]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('File not found');

        $client->mijnPS->uploadAssortment(
            '00000000-0000-0000-0000-000000000000',
            '/nonexistent/path/file.csv'
        );
    }

    public function test_upload_assortment_throws_exception_for_invalid_guid()
    {
        $testFile = $this->tempDir . '/test.csv';
        file_put_contents($testFile, 'test content');

        $client = $this->createMockPSClient([]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid assortment ID format');

        $client->mijnPS->uploadAssortment('invalid-guid', $testFile);
    }

    public function test_upload_assortment_throws_exception_for_short_guid()
    {
        $testFile = $this->tempDir . '/test.csv';
        file_put_contents($testFile, 'test content');

        $client = $this->createMockPSClient([]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid assortment ID format');

        $client->mijnPS->uploadAssortment('12345', $testFile);
    }

    public function test_upload_assortment_throws_api_exception_on_400()
    {
        $testFile = $this->tempDir . '/test.csv';
        file_put_contents($testFile, 'invalid content');

        $client = $this->createMockPSClient([
            $this->createErrorResponse('Invalid file format', 400)
        ]);

        $this->expectException(PSApiException::class);
        $this->expectExceptionMessage('Invalid file format');

        $client->mijnPS->uploadAssortment(
            '00000000-0000-0000-0000-000000000000',
            $testFile
        );
    }

    public function test_upload_assortment_throws_api_exception_on_401()
    {
        $testFile = $this->tempDir . '/test.csv';
        file_put_contents($testFile, 'test content');

        $client = $this->createMockPSClient([
            $this->createErrorResponse('Unauthorized', 401)
        ]);

        $this->expectException(PSApiException::class);
        $this->expectExceptionMessage('Unauthorized');

        $client->mijnPS->uploadAssortment(
            '00000000-0000-0000-0000-000000000000',
            $testFile
        );
    }

    public function test_upload_assortment_throws_api_exception_on_500()
    {
        $testFile = $this->tempDir . '/test.csv';
        file_put_contents($testFile, 'test content');

        $client = $this->createMockPSClient([
            new Response(500, [], 'Internal Server Error')
        ]);

        $this->expectException(PSApiException::class);

        $client->mijnPS->uploadAssortment(
            '00000000-0000-0000-0000-000000000000',
            $testFile
        );
    }

    public function test_upload_assortment_content_returns_true_on_success()
    {
        $client = $this->createMockPSClient([
            new Response(200)
        ]);

        $result = $client->mijnPS->uploadAssortmentContent(
            '00000000-0000-0000-0000-000000000000',
            'GTIN,ArticleNumber\n1234567890123,ABC-001',
            'assortment.csv'
        );

        $this->assertTrue($result);
    }

    public function test_upload_assortment_content_throws_exception_for_invalid_guid()
    {
        $client = $this->createMockPSClient([]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid assortment ID format');

        $client->mijnPS->uploadAssortmentContent(
            'not-a-guid',
            'content',
            'file.csv'
        );
    }

    public function test_upload_assortment_content_throws_exception_for_empty_content()
    {
        $client = $this->createMockPSClient([]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Content cannot be empty');

        $client->mijnPS->uploadAssortmentContent(
            '00000000-0000-0000-0000-000000000000',
            '',
            'file.csv'
        );
    }

    public function test_upload_assortment_content_throws_api_exception_on_403()
    {
        $client = $this->createMockPSClient([
            $this->createErrorResponse('Forbidden', 403)
        ]);

        $this->expectException(PSApiException::class);
        $this->expectExceptionMessage('Forbidden');

        $client->mijnPS->uploadAssortmentContent(
            '00000000-0000-0000-0000-000000000000',
            'test content',
            'file.csv'
        );
    }

    public function test_upload_assortment_content_throws_api_exception_on_500()
    {
        $client = $this->createMockPSClient([
            new Response(500, [], 'Internal Server Error')
        ]);

        $this->expectException(PSApiException::class);

        $client->mijnPS->uploadAssortmentContent(
            '00000000-0000-0000-0000-000000000000',
            'test content',
            'file.csv'
        );
    }

    public function test_upload_assortment_accepts_uppercase_guid()
    {
        $testFile = $this->tempDir . '/test.csv';
        file_put_contents($testFile, 'content');

        $client = $this->createMockPSClient([
            new Response(200)
        ]);

        $result = $client->mijnPS->uploadAssortment(
            'ABCDEF12-1234-5678-ABCD-123456789ABC',
            $testFile
        );

        $this->assertTrue($result);
    }

    public function test_upload_assortment_accepts_mixed_case_guid()
    {
        $testFile = $this->tempDir . '/test.csv';
        file_put_contents($testFile, 'content');

        $client = $this->createMockPSClient([
            new Response(200)
        ]);

        $result = $client->mijnPS->uploadAssortment(
            'AbCdEf12-1234-5678-aBcD-123456789aBc',
            $testFile
        );

        $this->assertTrue($result);
    }

    public function test_upload_assortment_with_xlsx_file()
    {
        $testFile = $this->tempDir . '/assortment.xlsx';
        file_put_contents($testFile, 'mock xlsx content');

        $client = $this->createMockPSClient([
            new Response(200)
        ]);

        $result = $client->mijnPS->uploadAssortment(
            '00000000-0000-0000-0000-000000000000',
            $testFile
        );

        $this->assertTrue($result);
    }
}
