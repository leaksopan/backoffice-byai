<?php

namespace Modules\MasterDataManagement\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Modules\MasterDataManagement\Services\CoaValidationService;

/**
 * Property Test: COA Format Validation
 * Feature: master-data-management, Property 5: COA Format Validation
 * Validates: Requirements 2.2
 */
class CoaFormatValidationTest extends TestCase
{
    use DatabaseMigrations;

    protected CoaValidationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CoaValidationService();
    }

    /**
     * Property: For any chart of account code input, the system should accept
     * only codes matching the format X-XX-XX-XX-XXX where X represents digits
     *
     * @test
     */
    public function property_validates_coa_format_correctly()
    {
        // Run 100 iterations untuk property-based testing
        for ($i = 0; $i < 100; $i++) {
            $this->runCoaFormatTest();
        }
    }

    private function runCoaFormatTest(): void
    {
        // Test 1: Valid format should pass
        $validCode = $this->generateValidCoaCode();
        $result = $this->service->validateCoaFormat($validCode);
        $this->assertTrue($result, "Valid COA code '{$validCode}' should pass validation");

        // Test 2: Invalid formats should fail
        $invalidCodes = $this->generateInvalidCoaCodes();
        foreach ($invalidCodes as $invalidCode) {
            $result = $this->service->validateCoaFormat($invalidCode);
            $this->assertFalse($result, "Invalid COA code '{$invalidCode}' should fail validation");
        }
    }

    /**
     * Generate valid COA code: X-XX-XX-XX-XXX
     */
    private function generateValidCoaCode(): string
    {
        return sprintf(
            '%d-%02d-%02d-%02d-%03d',
            rand(1, 9),
            rand(0, 99),
            rand(0, 99),
            rand(0, 99),
            rand(0, 999)
        );
    }

    /**
     * Generate various invalid COA codes
     */
    private function generateInvalidCoaCodes(): array
    {
        $invalidFormats = [];

        // Random selection of invalid formats
        $formats = [
            // Missing segments
            fn() => sprintf('%d-%02d-%02d-%02d', rand(1, 9), rand(0, 99), rand(0, 99), rand(0, 99)),
            // Too many segments
            fn() => sprintf('%d-%02d-%02d-%02d-%03d-%02d', rand(1, 9), rand(0, 99), rand(0, 99), rand(0, 99), rand(0, 999), rand(0, 99)),
            // Wrong digit count in first segment (2 digits instead of 1)
            fn() => sprintf('%02d-%02d-%02d-%02d-%03d', rand(10, 99), rand(0, 99), rand(0, 99), rand(0, 99), rand(0, 999)),
            // Wrong digit count in last segment (2 digits instead of 3)
            fn() => sprintf('%d-%02d-%02d-%02d-%02d', rand(1, 9), rand(0, 99), rand(0, 99), rand(0, 99), rand(0, 99)),
            // No separators
            fn() => sprintf('%d%02d%02d%02d%03d', rand(1, 9), rand(0, 99), rand(0, 99), rand(0, 99), rand(0, 999)),
            // Wrong separator (dot instead of dash)
            fn() => sprintf('%d.%02d.%02d.%02d.%03d', rand(1, 9), rand(0, 99), rand(0, 99), rand(0, 99), rand(0, 999)),
            // Contains letters
            fn() => sprintf('%d-%02d-XX-%02d-%03d', rand(1, 9), rand(0, 99), rand(0, 99), rand(0, 999)),
            // Empty string
            fn() => '',
            // Only separators
            fn() => '----',
        ];

        // Pick 3-5 random invalid formats
        $count = rand(3, 5);
        $selectedFormats = array_rand($formats, min($count, count($formats)));
        
        if (!is_array($selectedFormats)) {
            $selectedFormats = [$selectedFormats];
        }

        foreach ($selectedFormats as $index) {
            $invalidFormats[] = $formats[$index]();
        }

        return $invalidFormats;
    }

    /**
     * Test edge case: parseCoaStructure returns correct parts for valid codes
     *
     * @test
     */
    public function property_parse_coa_structure_returns_correct_parts()
    {
        for ($i = 0; $i < 50; $i++) {
            $code = $this->generateValidCoaCode();
            $parts = explode('-', $code);

            $result = $this->service->parseCoaStructure($code);

            $this->assertIsArray($result);
            $this->assertArrayHasKey('kelompok', $result);
            $this->assertArrayHasKey('jenis', $result);
            $this->assertArrayHasKey('objek', $result);
            $this->assertArrayHasKey('rincian_objek', $result);
            $this->assertArrayHasKey('sub_rincian_objek', $result);

            $this->assertEquals($parts[0], $result['kelompok']);
            $this->assertEquals($parts[1], $result['jenis']);
            $this->assertEquals($parts[2], $result['objek']);
            $this->assertEquals($parts[3], $result['rincian_objek']);
            $this->assertEquals($parts[4], $result['sub_rincian_objek']);
        }
    }

    /**
     * Test edge case: parseCoaStructure returns empty array for invalid codes
     *
     * @test
     */
    public function property_parse_coa_structure_returns_empty_for_invalid()
    {
        for ($i = 0; $i < 30; $i++) {
            $invalidCodes = $this->generateInvalidCoaCodes();
            
            foreach ($invalidCodes as $invalidCode) {
                $result = $this->service->parseCoaStructure($invalidCode);
                $this->assertEmpty($result, "Invalid code '{$invalidCode}' should return empty array");
            }
        }
    }

    /**
     * Test specific valid format examples
     *
     * @test
     */
    public function test_specific_valid_formats()
    {
        $validCodes = [
            '1-00-00-00-000',
            '9-99-99-99-999',
            '5-12-34-56-789',
            '1-01-01-01-001',
        ];

        foreach ($validCodes as $code) {
            $result = $this->service->validateCoaFormat($code);
            $this->assertTrue($result, "Code '{$code}' should be valid");
        }
    }

    /**
     * Test specific invalid format examples
     *
     * @test
     */
    public function test_specific_invalid_formats()
    {
        $invalidCodes = [
            '10-00-00-00-000',  // First segment too long
            '1-0-00-00-000',    // Second segment too short
            '1-00-0-00-000',    // Third segment too short
            '1-00-00-0-000',    // Fourth segment too short
            '1-00-00-00-00',    // Fifth segment too short
            '1-00-00-00-0000',  // Fifth segment too long
            'A-00-00-00-000',   // Contains letter
            '1/00/00/00/000',   // Wrong separator
            '1-00-00-00',       // Missing segment
        ];

        foreach ($invalidCodes as $code) {
            $result = $this->service->validateCoaFormat($code);
            $this->assertFalse($result, "Code '{$code}' should be invalid");
        }
    }
}
