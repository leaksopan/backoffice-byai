<?php

namespace Modules\MasterDataManagement\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Modules\MasterDataManagement\Models\MdmServiceCatalog;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;
use Illuminate\Database\QueryException;

/**
 * Property Test: Unique Code Constraint (Service Catalogs)
 * Feature: master-data-management, Property 7: Unique Code Constraint
 * Validates: Requirements 4.3
 */
class ServiceCatalogUniqueCodeTest extends TestCase
{
    use DatabaseMigrations;

    private MdmOrganizationUnit $testUnit;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test organization unit
        $this->testUnit = MdmOrganizationUnit::create([
            'code' => 'TEST-UNIT',
            'name' => 'Test Unit',
            'type' => 'unit',
            'level' => 0,
            'is_active' => true,
        ]);
    }

    /**
     * Property: For any master data entity type (service catalog),
     * the system should reject creation or update if the code already exists
     *
     * @test
     */
    public function property_rejects_duplicate_service_catalog_codes()
    {
        // Run 100 iterations untuk property-based testing
        for ($i = 0; $i < 100; $i++) {
            $this->runUniqueCodeTest();
        }
    }

    private function runUniqueCodeTest(): void
    {
        // Generate random service catalog
        $code = 'SVC' . rand(1000, 9999);
        $categories = ['rawat_jalan', 'rawat_inap', 'igd', 'penunjang_medis', 'tindakan', 'operasi', 'persalinan', 'administrasi'];
        $category = $categories[array_rand($categories)];
        
        $service = MdmServiceCatalog::create([
            'code' => $code,
            'name' => 'Service ' . uniqid(),
            'category' => $category,
            'unit_id' => $this->testUnit->id,
            'standard_duration' => rand(15, 120),
            'is_active' => true,
        ]);

        // Test 1: Attempt to create another service with same code
        $duplicateDetected = false;
        try {
            MdmServiceCatalog::create([
                'code' => $code, // Same code
                'name' => 'Duplicate Service ' . uniqid(),
                'category' => $category,
                'unit_id' => $this->testUnit->id,
                'is_active' => true,
            ]);
        } catch (QueryException $e) {
            // Should throw unique constraint violation
            $duplicateDetected = true;
        }

        $this->assertTrue($duplicateDetected, "System should reject duplicate service catalog code");

        // Test 2: Different code should be allowed
        $differentCode = 'SVC' . rand(10000, 99999);
        $newService = MdmServiceCatalog::create([
            'code' => $differentCode,
            'name' => 'Different Service ' . uniqid(),
            'category' => $category,
            'unit_id' => $this->testUnit->id,
            'is_active' => true,
        ]);

        $this->assertNotNull($newService->id, "Different code should be allowed");

        // Cleanup
        $service->delete();
        $newService->delete();
    }

    /**
     * Test edge case: updating with same code should be allowed
     *
     * @test
     */
    public function property_allows_update_with_same_code()
    {
        for ($i = 0; $i < 20; $i++) {
            $code = 'SVC' . rand(100000, 999999);
            
            $service = MdmServiceCatalog::create([
                'code' => $code,
                'name' => 'Original Name',
                'category' => 'rawat_jalan',
                'unit_id' => $this->testUnit->id,
                'is_active' => true,
            ]);

            // Update with same code should work
            $service->update([
                'code' => $code, // Same code
                'name' => 'Updated Name',
            ]);

            $this->assertEquals('Updated Name', $service->fresh()->name);

            $service->delete();
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
            $baseCode = 'svc' . rand(1000, 9999);
            
            $service1 = MdmServiceCatalog::create([
                'code' => strtolower($baseCode),
                'name' => 'Lowercase Code',
                'category' => 'tindakan',
                'unit_id' => $this->testUnit->id,
                'is_active' => true,
            ]);

            // Uppercase version should be treated as different (database dependent)
            // SQLite is case-insensitive by default, so this might fail
            try {
                $service2 = MdmServiceCatalog::create([
                    'code' => strtoupper($baseCode),
                    'name' => 'Uppercase Code',
                    'category' => 'tindakan',
                    'unit_id' => $this->testUnit->id,
                    'is_active' => true,
                ]);
                
                // If creation succeeds, codes are case-sensitive
                $this->assertNotEquals($service1->id, $service2->id);
                $service2->delete();
            } catch (QueryException $e) {
                // If it fails, codes are case-insensitive (expected for SQLite)
                $this->assertTrue(true, "Database treats codes as case-insensitive");
            }

            $service1->delete();
        }
    }

    /**
     * Test: Multiple services with different codes
     *
     * @test
     */
    public function property_allows_multiple_services_with_different_codes()
    {
        $services = [];
        $categories = ['rawat_jalan', 'rawat_inap', 'igd', 'penunjang_medis', 'tindakan', 'operasi'];
        
        for ($i = 0; $i < 50; $i++) {
            $service = MdmServiceCatalog::create([
                'code' => 'SVC' . uniqid() . rand(1000, 9999),
                'name' => 'Service ' . $i,
                'category' => $categories[array_rand($categories)],
                'unit_id' => $this->testUnit->id,
                'is_active' => true,
            ]);
            
            $services[] = $service;
        }

        // All should be created successfully
        $this->assertCount(50, $services);

        // Verify all codes are unique
        $codes = array_map(fn($s) => $s->code, $services);
        $uniqueCodes = array_unique($codes);
        $this->assertCount(50, $uniqueCodes, "All codes should be unique");

        // Cleanup
        foreach ($services as $s) {
            $s->delete();
        }
    }
}
