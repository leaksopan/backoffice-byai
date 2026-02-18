<?php

namespace Modules\MasterDataManagement\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;
use Modules\MasterDataManagement\Services\OrganizationHierarchyService;

/**
 * Property Test: Circular Reference Prevention
 * Feature: master-data-management, Property 1: Circular Reference Prevention
 * Validates: Requirements 1.2
 */
class OrganizationUnitCircularReferenceTest extends TestCase
{
    use DatabaseMigrations;

    protected OrganizationHierarchyService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OrganizationHierarchyService();
    }

    /**
     * Property: For any organization unit and proposed parent unit,
     * setting the parent relationship should be rejected if it would create
     * a circular reference in the hierarchy
     *
     * @test
     */
    public function property_prevents_circular_reference_in_hierarchy()
    {
        // Run 100 iterations untuk property-based testing
        for ($i = 0; $i < 100; $i++) {
            $this->runCircularReferenceTest();
        }
    }

    private function runCircularReferenceTest(): void
    {
        // Generate random hierarchy depth (1-5 levels)
        $depth = rand(1, 5);
        
        // Create a chain of units: A -> B -> C -> D -> E
        $units = [];
        $previousUnit = null;

        for ($j = 0; $j < $depth; $j++) {
            $unit = MdmOrganizationUnit::create([
                'code' => 'ORG' . uniqid(),
                'name' => 'Unit ' . uniqid(),
                'type' => ['installation', 'department', 'unit', 'section'][array_rand(['installation', 'department', 'unit', 'section'])],
                'parent_id' => $previousUnit?->id,
                'is_active' => true,
            ]);

            $this->service->updateHierarchyPath($unit);
            $units[] = $unit;
            $previousUnit = $unit;
        }

        if (count($units) < 2) {
            return;
        }

        // Test 1: Unit tidak bisa menjadi parent dari dirinya sendiri
        $randomUnit = $units[array_rand($units)];
        $result = $this->service->validateNoCircularReference($randomUnit->id, $randomUnit->id);
        $this->assertFalse($result, "Unit should not be able to be its own parent");

        // Test 2: Descendant tidak bisa menjadi parent dari ancestor
        if (count($units) >= 2) {
            $ancestor = $units[0];
            $descendant = $units[count($units) - 1];
            
            $result = $this->service->validateNoCircularReference($ancestor->id, $descendant->id);
            $this->assertFalse($result, "Descendant should not be able to be parent of ancestor");
        }

        // Test 3: Valid parent assignment (sibling atau unit dari branch lain)
        $newUnit = MdmOrganizationUnit::create([
            'code' => 'ORG' . uniqid(),
            'name' => 'New Unit ' . uniqid(),
            'type' => 'unit',
            'parent_id' => null,
            'is_active' => true,
        ]);

        $result = $this->service->validateNoCircularReference($newUnit->id, $units[0]->id);
        $this->assertTrue($result, "Valid parent assignment should be allowed");

        // Cleanup - delete in reverse order (children first)
        for ($i = count($units) - 1; $i >= 0; $i--) {
            $units[$i]->delete();
        }
        $newUnit->delete();
    }

    /**
     * Test edge case: null parent should always be valid
     *
     * @test
     */
    public function property_null_parent_is_always_valid()
    {
        for ($i = 0; $i < 20; $i++) {
            $unit = MdmOrganizationUnit::create([
                'code' => 'ORG' . uniqid(),
                'name' => 'Unit ' . uniqid(),
                'type' => 'installation',
                'parent_id' => null,
                'is_active' => true,
            ]);

            $result = $this->service->validateNoCircularReference($unit->id, null);
            $this->assertTrue($result, "Null parent should always be valid");

            $unit->delete();
        }
    }

    /**
     * Test edge case: non-existent parent should be valid (will fail at DB level)
     *
     * @test
     */
    public function property_nonexistent_parent_passes_circular_check()
    {
        for ($i = 0; $i < 20; $i++) {
            $unit = MdmOrganizationUnit::create([
                'code' => 'ORG' . uniqid(),
                'name' => 'Unit ' . uniqid(),
                'type' => 'installation',
                'parent_id' => null,
                'is_active' => true,
            ]);

            $nonExistentId = 999999 + $i;
            $result = $this->service->validateNoCircularReference($unit->id, $nonExistentId);
            $this->assertTrue($result, "Non-existent parent should pass circular reference check");

            $unit->delete();
        }
    }
}
