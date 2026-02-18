<?php

namespace Modules\MasterDataManagement\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;
use Modules\MasterDataManagement\Services\OrganizationHierarchyService;

/**
 * Property Test: Hierarchy Path Consistency
 * Feature: master-data-management, Property 2: Hierarchy Path Consistency
 * Validates: Requirements 1.4
 */
class OrganizationUnitHierarchyPathTest extends TestCase
{
    use DatabaseMigrations;

    protected OrganizationHierarchyService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OrganizationHierarchyService();
    }

    /**
     * Property: For any organization unit, when its parent is changed,
     * all descendant units should have their hierarchy paths updated
     * to reflect the new structure
     *
     * @test
     */
    public function property_hierarchy_path_updates_cascade_to_descendants()
    {
        // Run 100 iterations untuk property-based testing
        for ($i = 0; $i < 100; $i++) {
            $this->runHierarchyPathTest();
        }
    }

    private function runHierarchyPathTest(): void
    {
        // Create a hierarchy: A -> B -> C -> D
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

        $unitD = MdmOrganizationUnit::create([
            'code' => 'D' . uniqid(),
            'name' => 'Unit D',
            'type' => 'section',
            'parent_id' => $unitC->id,
            'is_active' => true,
        ]);
        $this->service->updateHierarchyPath($unitD);

        // Verify initial hierarchy paths
        $unitA->refresh();
        $unitB->refresh();
        $unitC->refresh();
        $unitD->refresh();

        $this->assertEquals("{$unitA->id}", $unitA->hierarchy_path);
        $this->assertEquals("{$unitA->id}/{$unitB->id}", $unitB->hierarchy_path);
        $this->assertEquals("{$unitA->id}/{$unitB->id}/{$unitC->id}", $unitC->hierarchy_path);
        $this->assertEquals("{$unitA->id}/{$unitB->id}/{$unitC->id}/{$unitD->id}", $unitD->hierarchy_path);

        // Create a new parent X
        $unitX = MdmOrganizationUnit::create([
            'code' => 'X' . uniqid(),
            'name' => 'Unit X',
            'type' => 'installation',
            'parent_id' => null,
            'is_active' => true,
        ]);
        $this->service->updateHierarchyPath($unitX);

        // Move B under X (B's parent changes from A to X)
        $unitB->parent_id = $unitX->id;
        $unitB->save();
        $this->service->updateHierarchyPath($unitB);

        // Verify all descendants of B have updated paths
        $unitB->refresh();
        $unitC->refresh();
        $unitD->refresh();

        $this->assertEquals("{$unitX->id}/{$unitB->id}", $unitB->hierarchy_path);
        $this->assertEquals("{$unitX->id}/{$unitB->id}/{$unitC->id}", $unitC->hierarchy_path);
        $this->assertEquals("{$unitX->id}/{$unitB->id}/{$unitC->id}/{$unitD->id}", $unitD->hierarchy_path);

        // Verify levels are correct
        $this->assertEquals(1, $unitB->level);
        $this->assertEquals(2, $unitC->level);
        $this->assertEquals(3, $unitD->level);

        // Cleanup - delete in reverse order
        $unitD->delete();
        $unitC->delete();
        $unitB->delete();
        $unitA->delete();
        $unitX->delete();
    }

    /**
     * Test edge case: root unit (no parent) should have simple path
     *
     * @test
     */
    public function property_root_unit_has_simple_hierarchy_path()
    {
        for ($i = 0; $i < 20; $i++) {
            $unit = MdmOrganizationUnit::create([
                'code' => 'ROOT' . uniqid(),
                'name' => 'Root Unit',
                'type' => 'installation',
                'parent_id' => null,
                'is_active' => true,
            ]);

            $this->service->updateHierarchyPath($unit);
            $unit->refresh();

            $this->assertEquals("{$unit->id}", $unit->hierarchy_path);
            $this->assertEquals(0, $unit->level);

            $unit->delete();
        }
    }

    /**
     * Test: hierarchy path contains all ancestor IDs in order
     *
     * @test
     */
    public function property_hierarchy_path_contains_all_ancestors()
    {
        for ($i = 0; $i < 20; $i++) {
            // Random depth 2-4
            $depth = rand(2, 4);
            $units = [];
            $previousUnit = null;

            for ($j = 0; $j < $depth; $j++) {
                $unit = MdmOrganizationUnit::create([
                    'code' => 'U' . uniqid(),
                    'name' => 'Unit ' . $j,
                    'type' => ['installation', 'department', 'unit', 'section'][array_rand(['installation', 'department', 'unit', 'section'])],
                    'parent_id' => $previousUnit?->id,
                    'is_active' => true,
                ]);

                $this->service->updateHierarchyPath($unit);
                $unit->refresh();

                // Verify path contains all ancestors
                $pathIds = explode('/', $unit->hierarchy_path);
                $this->assertCount($j + 1, $pathIds);
                $this->assertEquals($unit->id, end($pathIds));

                // Verify level matches depth
                $this->assertEquals($j, $unit->level);

                $units[] = $unit;
                $previousUnit = $unit;
            }

            // Cleanup
            for ($k = count($units) - 1; $k >= 0; $k--) {
                $units[$k]->delete();
            }
        }
    }
}
