<?php

namespace Modules\CostCenterManagement\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CostCenterManagement\Models\CostCenter;
use Modules\CostCenterManagement\Models\AllocationRule;
use Modules\CostCenterManagement\Models\AllocationRuleTarget;
use Modules\MasterDataManagement\Models\MdmOrganizationUnit;
use App\Models\User;

class CostCenterRelationshipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    /** @test */
    public function cost_center_has_parent_child_relationship()
    {
        // Create organization units
        $orgUnit1 = MdmOrganizationUnit::create([
            'code' => 'OU001',
            'name' => 'Organization Unit 1',
            'type' => 'installation',
            'is_active' => true,
        ]);
        
        $orgUnit2 = MdmOrganizationUnit::create([
            'code' => 'OU002',
            'name' => 'Organization Unit 2',
            'type' => 'department',
            'is_active' => true,
        ]);

        // Create parent cost center
        $parent = CostCenter::create([
            'code' => 'CC001',
            'name' => 'Parent Cost Center',
            'type' => 'administrative',
            'organization_unit_id' => $orgUnit1->id,
            'is_active' => true,
            'effective_date' => now(),
        ]);

        // Create child cost center
        $child = CostCenter::create([
            'code' => 'CC002',
            'name' => 'Child Cost Center',
            'type' => 'medical',
            'organization_unit_id' => $orgUnit2->id,
            'parent_id' => $parent->id,
            'is_active' => true,
            'effective_date' => now(),
        ]);

        // Test parent relationship
        $this->assertInstanceOf(CostCenter::class, $child->parent);
        $this->assertEquals($parent->id, $child->parent->id);

        // Test children relationship
        $this->assertCount(1, $parent->children);
        $this->assertEquals($child->id, $parent->children->first()->id);
    }

    /** @test */
    public function cost_center_belongs_to_organization_unit()
    {
        $orgUnit = MdmOrganizationUnit::create([
            'code' => 'OU003',
            'name' => 'Organization Unit 3',
            'type' => 'installation',
            'is_active' => true,
        ]);

        $costCenter = CostCenter::create([
            'code' => 'CC003',
            'name' => 'Test Cost Center',
            'type' => 'medical',
            'organization_unit_id' => $orgUnit->id,
            'is_active' => true,
            'effective_date' => now(),
        ]);

        $this->assertInstanceOf(MdmOrganizationUnit::class, $costCenter->organizationUnit);
        $this->assertEquals($orgUnit->id, $costCenter->organizationUnit->id);
    }

    /** @test */
    public function cost_center_belongs_to_manager()
    {
        $user = User::factory()->create();
        $orgUnit = MdmOrganizationUnit::create([
            'code' => 'OU004',
            'name' => 'Organization Unit 4',
            'type' => 'installation',
            'is_active' => true,
        ]);

        $costCenter = CostCenter::create([
            'code' => 'CC004',
            'name' => 'Test Cost Center',
            'type' => 'medical',
            'organization_unit_id' => $orgUnit->id,
            'manager_user_id' => $user->id,
            'is_active' => true,
            'effective_date' => now(),
        ]);

        $this->assertInstanceOf(User::class, $costCenter->manager);
        $this->assertEquals($user->id, $costCenter->manager->id);
    }

    /** @test */
    public function cost_center_has_allocation_rules_as_source()
    {
        $orgUnit1 = MdmOrganizationUnit::create([
            'code' => 'OU005',
            'name' => 'Organization Unit 5',
            'type' => 'installation',
            'is_active' => true,
        ]);
        
        $orgUnit2 = MdmOrganizationUnit::create([
            'code' => 'OU006',
            'name' => 'Organization Unit 6',
            'type' => 'department',
            'is_active' => true,
        ]);

        $sourceCostCenter = CostCenter::create([
            'code' => 'CC005',
            'name' => 'Source Cost Center',
            'type' => 'administrative',
            'organization_unit_id' => $orgUnit1->id,
            'is_active' => true,
            'effective_date' => now(),
        ]);

        $targetCostCenter = CostCenter::create([
            'code' => 'CC006',
            'name' => 'Target Cost Center',
            'type' => 'medical',
            'organization_unit_id' => $orgUnit2->id,
            'is_active' => true,
            'effective_date' => now(),
        ]);

        $allocationRule = AllocationRule::create([
            'code' => 'AR001',
            'name' => 'Test Allocation Rule',
            'source_cost_center_id' => $sourceCostCenter->id,
            'allocation_base' => 'percentage',
            'is_active' => true,
            'effective_date' => now(),
        ]);

        $this->assertCount(1, $sourceCostCenter->allocationRulesAsSource);
        $this->assertEquals($allocationRule->id, $sourceCostCenter->allocationRulesAsSource->first()->id);
    }

    /** @test */
    public function allocation_rule_has_targets_relationship()
    {
        $orgUnit1 = MdmOrganizationUnit::create([
            'code' => 'OU007',
            'name' => 'Organization Unit 7',
            'type' => 'installation',
            'is_active' => true,
        ]);
        
        $orgUnit2 = MdmOrganizationUnit::create([
            'code' => 'OU008',
            'name' => 'Organization Unit 8',
            'type' => 'department',
            'is_active' => true,
        ]);
        
        $orgUnit3 = MdmOrganizationUnit::create([
            'code' => 'OU009',
            'name' => 'Organization Unit 9',
            'type' => 'department',
            'is_active' => true,
        ]);

        $sourceCostCenter = CostCenter::create([
            'code' => 'CC007',
            'name' => 'Source Cost Center',
            'type' => 'administrative',
            'organization_unit_id' => $orgUnit1->id,
            'is_active' => true,
            'effective_date' => now(),
        ]);

        $targetCostCenter1 = CostCenter::create([
            'code' => 'CC008',
            'name' => 'Target Cost Center 1',
            'type' => 'medical',
            'organization_unit_id' => $orgUnit2->id,
            'is_active' => true,
            'effective_date' => now(),
        ]);

        $targetCostCenter2 = CostCenter::create([
            'code' => 'CC009',
            'name' => 'Target Cost Center 2',
            'type' => 'medical',
            'organization_unit_id' => $orgUnit3->id,
            'is_active' => true,
            'effective_date' => now(),
        ]);

        $allocationRule = AllocationRule::create([
            'code' => 'AR002',
            'name' => 'Test Allocation Rule',
            'source_cost_center_id' => $sourceCostCenter->id,
            'allocation_base' => 'percentage',
            'is_active' => true,
            'effective_date' => now(),
        ]);

        AllocationRuleTarget::create([
            'allocation_rule_id' => $allocationRule->id,
            'target_cost_center_id' => $targetCostCenter1->id,
            'allocation_percentage' => 60.00,
        ]);

        AllocationRuleTarget::create([
            'allocation_rule_id' => $allocationRule->id,
            'target_cost_center_id' => $targetCostCenter2->id,
            'allocation_percentage' => 40.00,
        ]);

        // Test allocation rule has targets
        $this->assertCount(2, $allocationRule->targets);
        
        // Test target belongs to allocation rule
        $target = $allocationRule->targets->first();
        $this->assertInstanceOf(AllocationRule::class, $target->allocationRule);
        $this->assertEquals($allocationRule->id, $target->allocationRule->id);

        // Test target belongs to cost center
        $this->assertInstanceOf(CostCenter::class, $target->targetCostCenter);
        $this->assertEquals($targetCostCenter1->id, $target->targetCostCenter->id);
    }

    /** @test */
    public function cost_center_active_scope_works()
    {
        $orgUnit1 = MdmOrganizationUnit::create([
            'code' => 'OU010',
            'name' => 'Organization Unit 10',
            'type' => 'installation',
            'is_active' => true,
        ]);
        
        $orgUnit2 = MdmOrganizationUnit::create([
            'code' => 'OU011',
            'name' => 'Organization Unit 11',
            'type' => 'department',
            'is_active' => true,
        ]);

        CostCenter::create([
            'code' => 'CC010',
            'name' => 'Active Cost Center',
            'type' => 'medical',
            'organization_unit_id' => $orgUnit1->id,
            'is_active' => true,
            'effective_date' => now(),
        ]);

        CostCenter::create([
            'code' => 'CC011',
            'name' => 'Inactive Cost Center',
            'type' => 'medical',
            'organization_unit_id' => $orgUnit2->id,
            'is_active' => false,
            'effective_date' => now(),
        ]);

        $activeCostCenters = CostCenter::active()->get();
        $this->assertCount(1, $activeCostCenters);
        $this->assertTrue($activeCostCenters->first()->is_active);
    }
}
