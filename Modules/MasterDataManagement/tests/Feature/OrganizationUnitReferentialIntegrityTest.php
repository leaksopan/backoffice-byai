<?php

namespace Modules\MasterDataManagement\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;
use Modules\MasterDataManagement\Services\OrganizationHierarchyService;

/**
 * Property Test: Referential Integrity Protection
 * Feature: master-data-management, Property 3: Referential Integrity Protection
 * Validates: Requirements 1.3
 */
class OrganizationUnitReferentialIntegrityTest extends TestCase
{
    use DatabaseMigrations;

    protected OrganizationHierarchyService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OrganizationHierarchyService();
    }

    /**
     * Property: For any organization unit that has child units,
     * deletion should be prevented to maintain referential integrity
     *
     * @test
     */
    public function property_prevents_deletion_of_units_with_children()
    {
        // Run 100 iterations untuk property-based testing
        for ($i = 0; $i < 100; $i++) {
            $this->runReferentialIntegrityTest();
        }
    }

    private function runReferentialIntegrityTest(): void
    {
        // Generate random number of children (1-5)
        $childrenCount = rand(1, 5);
        
        // Create parent unit
        $parent = MdmOrganizationUnit::create([
            'code' => 'PARENT' . uniqid(),
            'name' => 'Parent Unit ' . uniqid(),
            'type' => ['installation', 'department'][array_rand(['installation', 'department'])],
            'parent_id' => null,
            'is_active' => true,
        ]);
        $this->service->updateHierarchyPath($parent);

        // Create random number of children
        $children = [];
        for ($j = 0; $j < $childrenCount; $j++) {
            $child = MdmOrganizationUnit::create([
                'code' => 'CHILD' . uniqid(),
                'name' => 'Child Unit ' . uniqid(),
                'type' => ['department', 'unit', 'section'][array_rand(['department', 'unit', 'section'])],
                'parent_id' => $parent->id,
                'is_active' => true,
            ]);
            $this->service->updateHierarchyPath($child);
            $children[] = $child;
        }

        // Test: Parent dengan children tidak bisa dihapus
        $canDelete = $this->service->canDelete($parent);
        $this->assertFalse($canDelete, "Parent unit with children should not be deletable");

        // Verify parent masih ada di database
        $this->assertDatabaseHas('mdm_organization_units', [
            'id' => $parent->id,
            'code' => $parent->code,
        ]);

        // Test: Children bisa dihapus (tidak punya children sendiri)
        foreach ($children as $child) {
            $canDeleteChild = $this->service->canDelete($child);
            $this->assertTrue($canDeleteChild, "Child unit without children should be deletable");
        }

        // Cleanup - delete children first, then parent
        foreach ($children as $child) {
            $child->delete();
        }
        
        // After children deleted, parent should be deletable
        $canDeleteAfter = $this->service->canDelete($parent);
        $this->assertTrue($canDeleteAfter, "Parent unit without children should be deletable");
        
        $parent->delete();
    }

    /**
     * Test edge case: unit without children should always be deletable
     *
     * @test
     */
    public function property_unit_without_children_is_deletable()
    {
        for ($i = 0; $i < 20; $i++) {
            $unit = MdmOrganizationUnit::create([
                'code' => 'LEAF' . uniqid(),
                'name' => 'Leaf Unit ' . uniqid(),
                'type' => ['unit', 'section'][array_rand(['unit', 'section'])],
                'parent_id' => null,
                'is_active' => true,
            ]);

            $canDelete = $this->service->canDelete($unit);
            $this->assertTrue($canDelete, "Unit without children should be deletable");

            $unit->delete();
        }
    }

    /**
     * Test: nested hierarchy - only leaf nodes can be deleted
     *
     * @test
     */
    public function property_only_leaf_nodes_are_deletable_in_hierarchy()
    {
        for ($i = 0; $i < 20; $i++) {
            // Create hierarchy: A -> B -> C
            $unitA = MdmOrganizationUnit::create([
                'code' => 'A' . uniqid(),
                'name' => 'Unit A',
                'type' => 'installation',
                'parent_id' => null,
                'is_active' => true,
            ]);
            $this->service->updateHierarchyPath($unitA);

            $unitB = MdmOrganizationUnit::create([
                'code' => 'B' . uniqid(),
                'name' => 'Unit B',
                'type' => 'department',
                'parent_id' => $unitA->id,
                'is_active' => true,
            ]);
            $this->service->updateHierarchyPath($unitB);

            $unitC = MdmOrganizationUnit::create([
                'code' => 'C' . uniqid(),
                'name' => 'Unit C',
                'type' => 'unit',
                'parent_id' => $unitB->id,
                'is_active' => true,
            ]);
            $this->service->updateHierarchyPath($unitC);

            // Test: A cannot be deleted (has child B)
            $this->assertFalse($this->service->canDelete($unitA), "Root with children should not be deletable");

            // Test: B cannot be deleted (has child C)
            $this->assertFalse($this->service->canDelete($unitB), "Middle node with children should not be deletable");

            // Test: C can be deleted (leaf node)
            $this->assertTrue($this->service->canDelete($unitC), "Leaf node should be deletable");

            // Cleanup - delete in reverse order
            $unitC->delete();
            
            // After C deleted, B becomes leaf and should be deletable
            $this->assertTrue($this->service->canDelete($unitB), "B should be deletable after C is removed");
            $unitB->delete();
            
            // After B deleted, A becomes leaf and should be deletable
            $this->assertTrue($this->service->canDelete($unitA), "A should be deletable after B is removed");
            $unitA->delete();
        }
    }

    /**
     * Test: multiple children scenario
     *
     * @test
     */
    public function property_unit_with_multiple_children_cannot_be_deleted()
    {
        for ($i = 0; $i < 20; $i++) {
            $parent = MdmOrganizationUnit::create([
                'code' => 'PARENT' . uniqid(),
                'name' => 'Parent Unit',
                'type' => 'installation',
                'parent_id' => null,
                'is_active' => true,
            ]);
            $this->service->updateHierarchyPath($parent);

            // Create 3-7 children
            $childCount = rand(3, 7);
            $children = [];
            
            for ($j = 0; $j < $childCount; $j++) {
                $child = MdmOrganizationUnit::create([
                    'code' => 'CHILD' . uniqid(),
                    'name' => 'Child ' . $j,
                    'type' => 'department',
                    'parent_id' => $parent->id,
                    'is_active' => true,
                ]);
                $this->service->updateHierarchyPath($child);
                $children[] = $child;
            }

            // Parent should not be deletable
            $this->assertFalse($this->service->canDelete($parent), "Parent with multiple children should not be deletable");

            // Delete all but one child
            for ($j = 0; $j < $childCount - 1; $j++) {
                $children[$j]->delete();
            }

            // Parent still has one child, should not be deletable
            $this->assertFalse($this->service->canDelete($parent), "Parent with one remaining child should not be deletable");

            // Delete last child
            $children[$childCount - 1]->delete();

            // Now parent should be deletable
            $this->assertTrue($this->service->canDelete($parent), "Parent without children should be deletable");

            $parent->delete();
        }
    }
}
