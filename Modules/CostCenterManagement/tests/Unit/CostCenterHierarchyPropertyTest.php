<?php

namespace Modules\CostCenterManagement\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\CostCenterManagement\Models\CostCenter;
use Modules\CostCenterManagement\Services\CostCenterHierarchyService;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;

/**
 * Property-Based Tests untuk Cost Center Hierarchy
 * 
 * Feature: cost-center-management
 * Minimum iterations: 100 per property test
 */
class CostCenterHierarchyPropertyTest extends TestCase
{
    use RefreshDatabase;

    protected CostCenterHierarchyService $hierarchyService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->hierarchyService = new CostCenterHierarchyService();
    }

    protected function tearDown(): void
    {
        if (DB::transactionLevel() > 0) {
            DB::rollBack();
        }
        parent::tearDown();
    }

    /**
     * Property 1: Circular Reference Prevention
     * 
     * For any cost center and proposed parent cost center, setting the parent 
     * relationship should be rejected if it would create a circular reference 
     * in the hierarchy (i.e., if the proposed parent is a descendant of the cost center)
     * 
     * Validates: Requirements 2.2
     * 
     * @test
     * Feature: cost-center-management, Property 1: Circular Reference Prevention
     */
    public function property_circular_reference_prevention()
    {
        $iterations = 100;

        for ($i = 0; $i < $iterations; $i++) {
            DB::beginTransaction();

            try {
                // Create a hierarchy: A -> B -> C
                $costCenterA = $this->generateRandomCostCenter(['parent_id' => null]);
                $this->hierarchyService->updateHierarchyPath($costCenterA);

                $costCenterB = $this->generateRandomCostCenter(['parent_id' => $costCenterA->id]);
                $this->hierarchyService->updateHierarchyPath($costCenterB);

                $costCenterC = $this->generateRandomCostCenter(['parent_id' => $costCenterB->id]);
                $this->hierarchyService->updateHierarchyPath($costCenterC);

                // Test 1: Setting A's parent to itself should be invalid
                $result = $this->hierarchyService->validateNoCircularReference($costCenterA->id, $costCenterA->id);
                $this->assertFalse($result, 
                    "Iteration {$i}: Cost center cannot be its own parent");

                // Test 2: Setting A's parent to B (its child) should be invalid
                $result = $this->hierarchyService->validateNoCircularReference($costCenterA->id, $costCenterB->id);
                $this->assertFalse($result, 
                    "Iteration {$i}: Cost center cannot have its child as parent");

                // Test 3: Setting A's parent to C (its grandchild) should be invalid
                $result = $this->hierarchyService->validateNoCircularReference($costCenterA->id, $costCenterC->id);
                $this->assertFalse($result, 
                    "Iteration {$i}: Cost center cannot have its descendant as parent");

                // Test 4: Setting C's parent to A (its grandparent) should be valid
                $result = $this->hierarchyService->validateNoCircularReference($costCenterC->id, $costCenterA->id);
                $this->assertTrue($result, 
                    "Iteration {$i}: Cost center can have its ancestor as parent (restructuring)");

                // Test 5: Setting parent to null should always be valid
                $result = $this->hierarchyService->validateNoCircularReference($costCenterA->id, null);
                $this->assertTrue($result, 
                    "Iteration {$i}: Setting parent to null should always be valid");

                // Test 6: Create random hierarchy and test circular reference
                $depth = rand(2, 5);
                $hierarchy = $this->createRandomHierarchy($depth);
                
                // Try to set root's parent to any descendant
                $root = $hierarchy[0];
                $randomDescendant = $hierarchy[rand(1, count($hierarchy) - 1)];
                
                $result = $this->hierarchyService->validateNoCircularReference($root->id, $randomDescendant->id);
                $this->assertFalse($result, 
                    "Iteration {$i}: Root cannot have any descendant as parent");

            } finally {
                DB::rollBack();
            }
        }

        $this->assertTrue(true, "Circular reference prevention property holds for all iterations");
    }

    /**
     * Property 2: Hierarchy Path Consistency
     * 
     * For any cost center, when its parent is changed, all descendant cost centers 
     * should have their hierarchy paths updated to reflect the new structure 
     * within the same transaction
     * 
     * Validates: Requirements 2.3
     * 
     * @test
     * Feature: cost-center-management, Property 2: Hierarchy Path Consistency
     */
    public function property_hierarchy_path_consistency()
    {
        $iterations = 100;

        for ($i = 0; $i < $iterations; $i++) {
            DB::beginTransaction();

            try {
                // Create initial hierarchy: A -> B -> C -> D
                $costCenterA = $this->generateRandomCostCenter(['parent_id' => null]);
                $this->hierarchyService->updateHierarchyPath($costCenterA);

                $costCenterB = $this->generateRandomCostCenter(['parent_id' => $costCenterA->id]);
                $this->hierarchyService->updateHierarchyPath($costCenterB);

                $costCenterC = $this->generateRandomCostCenter(['parent_id' => $costCenterB->id]);
                $this->hierarchyService->updateHierarchyPath($costCenterC);

                $costCenterD = $this->generateRandomCostCenter(['parent_id' => $costCenterC->id]);
                $this->hierarchyService->updateHierarchyPath($costCenterD);

                // Verify initial paths
                $costCenterA->refresh();
                $costCenterB->refresh();
                $costCenterC->refresh();
                $costCenterD->refresh();

                $this->assertEquals("{$costCenterA->id}", $costCenterA->hierarchy_path);
                $this->assertEquals("{$costCenterA->id}/{$costCenterB->id}", $costCenterB->hierarchy_path);
                $this->assertEquals("{$costCenterA->id}/{$costCenterB->id}/{$costCenterC->id}", $costCenterC->hierarchy_path);
                $this->assertEquals("{$costCenterA->id}/{$costCenterB->id}/{$costCenterC->id}/{$costCenterD->id}", $costCenterD->hierarchy_path);

                // Change B's parent to null (make it root)
                $costCenterB->parent_id = null;
                $costCenterB->save();
                $this->hierarchyService->updateHierarchyPath($costCenterB);

                // Verify all paths updated
                $costCenterB->refresh();
                $costCenterC->refresh();
                $costCenterD->refresh();

                $this->assertEquals("{$costCenterB->id}", $costCenterB->hierarchy_path,
                    "Iteration {$i}: B should be root now");
                $this->assertEquals("{$costCenterB->id}/{$costCenterC->id}", $costCenterC->hierarchy_path,
                    "Iteration {$i}: C's path should reflect B as root");
                $this->assertEquals("{$costCenterB->id}/{$costCenterC->id}/{$costCenterD->id}", $costCenterD->hierarchy_path,
                    "Iteration {$i}: D's path should reflect B as root");

                // Verify levels are correct
                $this->assertEquals(0, $costCenterB->level);
                $this->assertEquals(1, $costCenterC->level);
                $this->assertEquals(2, $costCenterD->level);

                // Test with random hierarchy
                $depth = rand(3, 6);
                $hierarchy = $this->createRandomHierarchy($depth);
                
                // Pick a random node (not root, not leaf)
                $middleIndex = rand(1, count($hierarchy) - 2);
                $middleNode = $hierarchy[$middleIndex];
                
                // Change its parent to root
                $root = $hierarchy[0];
                $middleNode->parent_id = $root->id;
                $middleNode->save();
                $this->hierarchyService->updateHierarchyPath($middleNode);
                
                // Verify path consistency
                $middleNode->refresh();
                $this->assertStringStartsWith("{$root->id}/", $middleNode->hierarchy_path,
                    "Iteration {$i}: Middle node path should start with root");
                
                // Verify all descendants updated
                $descendants = $this->hierarchyService->getDescendants($middleNode->id);
                foreach ($descendants as $descendant) {
                    $this->assertStringContainsString((string)$middleNode->id, $descendant->hierarchy_path,
                        "Iteration {$i}: Descendant path should contain middle node");
                }

            } finally {
                DB::rollBack();
            }
        }

        $this->assertTrue(true, "Hierarchy path consistency property holds for all iterations");
    }

    /**
     * Property 3: Referential Integrity Protection
     * 
     * For any cost center that has one or more child cost centers, deletion 
     * attempts should be rejected with an appropriate error message
     * 
     * Validates: Requirements 2.5
     * 
     * @test
     * Feature: cost-center-management, Property 3: Referential Integrity Protection
     */
    public function property_referential_integrity_protection()
    {
        $iterations = 100;

        for ($i = 0; $i < $iterations; $i++) {
            DB::beginTransaction();

            try {
                // Create hierarchy with random depth
                $depth = rand(2, 5);
                $hierarchy = $this->createRandomHierarchy($depth);

                // Test each node except leaves
                for ($j = 0; $j < count($hierarchy) - 1; $j++) {
                    $node = $hierarchy[$j];
                    
                    // Node has children, should not be deletable
                    $canDelete = $this->hierarchyService->canDelete($node);
                    $this->assertFalse($canDelete, 
                        "Iteration {$i}, Node {$j}: Cost center with children should not be deletable");

                    // Verify children count
                    $childrenCount = $node->children()->count();
                    $this->assertGreaterThan(0, $childrenCount,
                        "Iteration {$i}, Node {$j}: Should have at least one child");
                }

                // Test leaf nodes (should be deletable)
                $leaf = $hierarchy[count($hierarchy) - 1];
                $canDelete = $this->hierarchyService->canDelete($leaf);
                $this->assertTrue($canDelete, 
                    "Iteration {$i}: Leaf node should be deletable");

                // Create standalone cost center (no children)
                $standalone = $this->generateRandomCostCenter(['parent_id' => null]);
                $canDelete = $this->hierarchyService->canDelete($standalone);
                $this->assertTrue($canDelete, 
                    "Iteration {$i}: Standalone cost center should be deletable");

                // Test database constraint
                $parent = $hierarchy[0];
                try {
                    $parent->delete();
                    $this->fail("Iteration {$i}: Should not be able to delete cost center with children");
                } catch (\Exception $e) {
                    // Expected: foreign key constraint should prevent deletion
                    $this->assertTrue(true);
                }

            } finally {
                DB::rollBack();
            }
        }

        $this->assertTrue(true, "Referential integrity protection property holds for all iterations");
    }

    // Helper methods

    private function generateRandomOrganizationUnit(array $overrides = []): MdmOrganizationUnit
    {
        $types = ['installation', 'department', 'unit', 'section'];
        
        $data = array_merge([
            'code' => 'OU-' . rand(10000, 99999),
            'name' => 'Org Unit ' . rand(1000, 9999),
            'type' => $types[array_rand($types)],
            'is_active' => true,
        ], $overrides);

        return MdmOrganizationUnit::create($data);
    }

    private function generateRandomCostCenter(array $overrides = []): CostCenter
    {
        $types = ['medical', 'non_medical', 'administrative', 'profit_center'];
        
        $data = array_merge([
            'code' => 'CC-' . rand(10000, 99999),
            'name' => 'Cost Center ' . rand(1000, 9999),
            'type' => $types[array_rand($types)],
            'is_active' => true,
            'effective_date' => now(),
        ], $overrides);

        if (!isset($data['organization_unit_id'])) {
            $orgUnit = $this->generateRandomOrganizationUnit();
            $data['organization_unit_id'] = $orgUnit->id;
        }

        return CostCenter::create($data);
    }

    /**
     * Create a random hierarchy of cost centers
     * 
     * @param int $depth
     * @return array Array of CostCenter models from root to leaf
     */
    private function createRandomHierarchy(int $depth): array
    {
        $hierarchy = [];
        
        // Create root
        $root = $this->generateRandomCostCenter(['parent_id' => null]);
        $this->hierarchyService->updateHierarchyPath($root);
        $hierarchy[] = $root;

        // Create chain
        $parent = $root;
        for ($i = 1; $i < $depth; $i++) {
            $child = $this->generateRandomCostCenter(['parent_id' => $parent->id]);
            $this->hierarchyService->updateHierarchyPath($child);
            $hierarchy[] = $child;
            $parent = $child;
        }

        return $hierarchy;
    }
}
