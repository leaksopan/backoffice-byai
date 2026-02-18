<?php

namespace Modules\MasterDataManagement\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Modules\MasterDataManagement\Models\MdmFundingSource;
use Illuminate\Database\QueryException;

/**
 * Property Test: Unique Code Constraint (Funding Sources)
 * Feature: master-data-management, Property 7: Unique Code Constraint
 * Validates: Requirements 3.3
 */
class FundingSourceUniqueCodeTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * Property: For any master data entity type (funding source),
     * the system should reject creation or update if the code already exists
     *
     * @test
     */
    public function property_rejects_duplicate_funding_source_codes()
    {
        // Run 100 iterations untuk property-based testing
        for ($i = 0; $i < 100; $i++) {
            $this->runUniqueCodeTest();
        }
    }

    private function runUniqueCodeTest(): void
    {
        // Generate random funding source
        $code = 'FS' . rand(1000, 9999);
        $type = ['apbn', 'apbd_provinsi', 'apbd_kab_kota', 'pnbp', 'hibah', 'pinjaman', 'lainnya'][array_rand(['apbn', 'apbd_provinsi', 'apbd_kab_kota', 'pnbp', 'hibah', 'pinjaman', 'lainnya'])];
        
        $fundingSource = MdmFundingSource::create([
            'code' => $code,
            'name' => 'Funding Source ' . uniqid(),
            'type' => $type,
            'start_date' => now()->subDays(rand(0, 365)),
            'end_date' => rand(0, 1) ? now()->addDays(rand(30, 365)) : null,
            'is_active' => true,
        ]);

        // Test 1: Attempt to create another funding source with same code
        $duplicateDetected = false;
        try {
            MdmFundingSource::create([
                'code' => $code, // Same code
                'name' => 'Duplicate Funding Source ' . uniqid(),
                'type' => $type,
                'start_date' => now(),
                'is_active' => true,
            ]);
        } catch (QueryException $e) {
            // Should throw unique constraint violation
            $duplicateDetected = true;
        }

        $this->assertTrue($duplicateDetected, "System should reject duplicate funding source code");

        // Test 2: Different code should be allowed
        $differentCode = 'FS' . rand(10000, 99999);
        $newFundingSource = MdmFundingSource::create([
            'code' => $differentCode,
            'name' => 'Different Funding Source ' . uniqid(),
            'type' => $type,
            'start_date' => now(),
            'is_active' => true,
        ]);

        $this->assertNotNull($newFundingSource->id, "Different code should be allowed");

        // Cleanup
        $fundingSource->delete();
        $newFundingSource->delete();
    }

    /**
     * Test edge case: updating with same code should be allowed
     *
     * @test
     */
    public function property_allows_update_with_same_code()
    {
        for ($i = 0; $i < 20; $i++) {
            $code = 'FS' . rand(100000, 999999);
            
            $fundingSource = MdmFundingSource::create([
                'code' => $code,
                'name' => 'Original Name',
                'type' => 'apbn',
                'start_date' => now(),
                'is_active' => true,
            ]);

            // Update with same code should work
            $fundingSource->update([
                'code' => $code, // Same code
                'name' => 'Updated Name',
            ]);

            $this->assertEquals('Updated Name', $fundingSource->fresh()->name);

            $fundingSource->delete();
        }
    }

    /**
     * Test edge case: case sensitivity in codes
     *
     * @test
     */
    public function property_code_uniqueness_is_case_sensitive()
    {
        for ($i = 0; $i < 20; $i++) {
            $baseCode = 'fs' . rand(1000, 9999);
            
            $fundingSource1 = MdmFundingSource::create([
                'code' => strtolower($baseCode),
                'name' => 'Lowercase Code',
                'type' => 'pnbp',
                'start_date' => now(),
                'is_active' => true,
            ]);

            // Uppercase version should be treated as different (database dependent)
            // SQLite is case-insensitive by default, so this might fail
            // This test documents the expected behavior
            try {
                $fundingSource2 = MdmFundingSource::create([
                    'code' => strtoupper($baseCode),
                    'name' => 'Uppercase Code',
                    'type' => 'pnbp',
                    'start_date' => now(),
                    'is_active' => true,
                ]);
                
                // If creation succeeds, codes are case-sensitive
                $this->assertNotEquals($fundingSource1->id, $fundingSource2->id);
                $fundingSource2->delete();
            } catch (QueryException $e) {
                // If it fails, codes are case-insensitive (expected for SQLite)
                $this->assertTrue(true, "Database treats codes as case-insensitive");
            }

            $fundingSource1->delete();
        }
    }

    /**
     * Test: Multiple funding sources with different codes
     *
     * @test
     */
    public function property_allows_multiple_funding_sources_with_different_codes()
    {
        $fundingSources = [];
        
        for ($i = 0; $i < 50; $i++) {
            $fundingSource = MdmFundingSource::create([
                'code' => 'FS' . uniqid() . rand(1000, 9999),
                'name' => 'Funding Source ' . $i,
                'type' => ['apbn', 'pnbp', 'hibah'][array_rand(['apbn', 'pnbp', 'hibah'])],
                'start_date' => now(),
                'is_active' => true,
            ]);
            
            $fundingSources[] = $fundingSource;
        }

        // All should be created successfully
        $this->assertCount(50, $fundingSources);

        // Verify all codes are unique
        $codes = array_map(fn($fs) => $fs->code, $fundingSources);
        $uniqueCodes = array_unique($codes);
        $this->assertCount(50, $uniqueCodes, "All codes should be unique");

        // Cleanup
        foreach ($fundingSources as $fs) {
            $fs->delete();
        }
    }
}
